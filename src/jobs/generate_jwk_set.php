<?php
try {
    define('ROOT_DIR', dirname(__DIR__));
    define('DESIRED_AMOUNT_OF_KEY', 5);

    require_once ROOT_DIR . '/vendor/autoload.php';

    $container = (new \DI\ContainerBuilder())
        ->addDefinitions(ROOT_DIR . '/definitions.php')
        ->build();

    $keyset = new \SimpleJWT\Keys\KeySet();

    $internalJwksFile = '/keys/internal_jwk_set.json';

    if (file_exists($internalJwksFile)) {
        $content = file_get_contents($internalJwksFile);
        $keyset->load($content);
    }

    $now = time();
    $newest = $now;
    $ids = [];

    // Remove expired keys
    foreach ($keyset->getKeys() as $key) {
        /** @var \SimpleJWT\Keys\Key $key */
        $data = $key->getKeyData();
        if (!isset($data['exp']) || !is_numeric($data['exp']) || $now > $data['exp']) {
            $keyset->remove($key);
            continue;
        }
        if ($data['exp'] > $newest) {
            $newest = $data['exp'];
        }
        $ids[] = $key->getKeyId();
    }

    $keyCount = count($keyset->getKeys());
    $missingAmount = DESIRED_AMOUNT_OF_KEY - $keyCount;

    for ($i = 0; $i < $missingAmount; $i++) {
        do {
            $kid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        } while(in_array($kid, $ids));
        $offset = 24+(24*$i);
        $keyset->add(new \SimpleJWT\Keys\SymmetricKey([
            'kty' => \SimpleJWT\Keys\SymmetricKey::KTY,
            'k' => \SimpleJWT\Util\Util::base64url_encode(random_bytes(64)),
            'alg' => 'HS256',
            'kid' => $kid,
            'exp' => strtotime("+{$offset} hours", $newest),
        ], 'php'));
        $ids[] = $kid;
    }

    if (file_put_contents($internalJwksFile, $keyset->toJWKS(), LOCK_EX) === false) {
        throw new Exception('Failed to save file.');
    }
    if (!chmod($internalJwksFile, 0777)) {
        throw new Exception('Failed to change permissions of output file: "' . $internalJwksFile . '".');
    }
} catch (\Throwable $throwable) {
    echo $throwable->getMessage() . PHP_EOL;
    exit(1);
}