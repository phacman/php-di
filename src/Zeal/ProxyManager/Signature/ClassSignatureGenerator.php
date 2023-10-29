<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\Signature;

use Laminas\Code\Exception\InvalidArgumentException;
use Laminas\Code\Generator\AbstractMemberGenerator;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\PropertyGenerator;

/**
 * Applies a signature to a given class generator.
 */
final class ClassSignatureGenerator implements ClassSignatureGeneratorInterface
{
    private SignatureGeneratorInterface $signatureGenerator;

    public function __construct(SignatureGeneratorInterface $signatureGenerator)
    {
        $this->signatureGenerator = $signatureGenerator;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function addSignature(ClassGenerator $classGenerator, array $parameters) : ClassGenerator
    {
        $classGenerator->addPropertyFromGenerator(new PropertyGenerator(
            'signature' . $this->signatureGenerator->generateSignatureKey($parameters),
            $this->signatureGenerator->generateSignature($parameters),
            AbstractMemberGenerator::FLAG_STATIC | AbstractMemberGenerator::FLAG_PRIVATE
        ));

        return $classGenerator;
    }
}
