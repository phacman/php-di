<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\Signature;

use DI\Zeal\ProxyManager\Inflector\Util\ParameterEncoder;
use DI\Zeal\ProxyManager\Inflector\Util\ParameterHasher;

final class SignatureGenerator implements SignatureGeneratorInterface
{
    private ParameterEncoder $parameterEncoder;
    private ParameterHasher $parameterHasher;

    public function __construct()
    {
        $this->parameterEncoder = new ParameterEncoder();
        $this->parameterHasher = new ParameterHasher();
    }

    public function generateSignature(array $parameters) : string
    {
        return $this->parameterEncoder->encodeParameters($parameters);
    }

    public function generateSignatureKey(array $parameters) : string
    {
        return $this->parameterHasher->hashParameters($parameters);
    }
}
