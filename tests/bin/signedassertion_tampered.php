#!/usr/bin/env php
<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php');

use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

$document = DOMDocumentFactory::fromFile(dirname(dirname(__FILE__)) . '/resources/xml/saml_Assertion.xml');
$assertion = Assertion::fromXML($document->documentElement);

$signer = (new SignatureAlgorithmFactory())->getAlgorithm(
    C::SIG_RSA_SHA256,
    PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY),
);

$unsignedAssertion = Assertion::fromXML($document->documentElement);
$unsignedAssertion->sign($signer);
echo str_replace('127.0.0.1', '127.0.0.2', strval($unsignedAssertion->toXML()->ownerDocument->saveXML()));