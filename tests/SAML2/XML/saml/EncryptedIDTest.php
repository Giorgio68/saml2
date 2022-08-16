<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Configuration;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\BaseID;
use SimpleSAML\SAML2\XML\saml\EncryptedID;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\Test\SAML2\CustomBaseID;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\Key\PublicKey;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\xenc\CarriedKeyName;
use SimpleSAML\XMLSecurity\XML\xenc\CipherData;
use SimpleSAML\XMLSecurity\XML\xenc\CipherValue;
use SimpleSAML\XMLSecurity\XML\xenc\DataReference;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedData;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedKey;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptionMethod;
use SimpleSAML\XMLSecurity\XML\xenc\ReferenceList;

use function dirname;
use function strval;

/**
 * Class EncryptedIDTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\EncryptedID
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class EncryptedIDTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    public function setup(): void
    {
        $this->testedClass = EncryptedID::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_EncryptedID.xml'
        );
    }


    /**
     */
    public function tearDown(): void
    {
        ContainerSingleton::setContainer(new MockContainer());
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $ed = new EncryptedData(
            new CipherData(new CipherValue('iFz/8KASJCLCAHqaAKhZXWOG/TPZlgTxcQ25lTGxdSdEsGYz7cg5lfZAbcN3UITCP9MkJsyjMlRsQouIqBkoPCGZz8NXibDkQ8OUeE7JdkFgKvgUMXawp+uDL4gHR8L7l6SPAmWZU3Hx/Wg9pTJBOpTjwoS0')),
            null,
            null,
            null,
            null,
            new EncryptionMethod('http://www.w3.org/2009xmlenc11#aes256-gcm'),
            new KeyInfo([
                new EncryptedKey(
                    new CipherData(new CipherValue('GMhpk09X+quNC/SsnxcDglZU/DCLAu9bMJ5bPcgaBK4s3F1eXciU8hlOYNaskSwP86HmA704NbzSDOHAgN6ckR+iCssxA7XCBjz0hltsgfn5p9Rh8qKtKltiXvxo/xXTcSXXZXNcE0R2KTya0P4DjZvYYgbIls/AH8ZyDV07ntI=')),
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    new EncryptionMethod('http://www.w3.org/2009/xmlenc11#rsa-oaep'),
                )
            ]),
        );
        $ek = new EncryptedKey(
            new CipherData(new CipherValue('GMhpk09X+quNC/SsnxcDglZU/DCLAu9bMJ5bPcgaBK4s3F1eXciU8hlOYNaskSwP86HmA704NbzSDOHAgN6ckR+iCssxA7XCBjz0hltsgfn5p9Rh8qKtKltiXvxo/xXTcSXXZXNcE0R2KTya0P4DjZvYYgbIls/AH8ZyDV07ntI=')),
            'Encrypted_KEY_ID',
            null,
            null,
            null,
            'some_ENTITY_ID',
            new CarriedKeyName('Name of the key'),
            new EncryptionMethod('http://www.w3.org/2009/xmlenc11#rsa-oaep'),
            null,
            new ReferenceList(
                [new DataReference('#Encrypted_DATA_ID')]
            )
        );
        $eid = new EncryptedID($ed, [$ek]);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($eid)
        );
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
    {
        $ed = new EncryptedData(
            new CipherData(new CipherValue('iFz/8KASJCLCAHqaAKhZXWOG/TPZlgTxcQ25lTGxdSdEsGYz7cg5lfZAbcN3UITCP9MkJsyjMlRsQouIqBkoPCGZz8NXibDkQ8OUeE7JdkFgKvgUMXawp+uDL4gHR8L7l6SPAmWZU3Hx/Wg9pTJBOpTjwoS0')),
            null,
            null,
            null,
            null,
            new EncryptionMethod('http://www.w3.org/2001/04/xmlenc#aes128-cbc'),
            new KeyInfo([
                new EncryptedKey(
                    new CipherData(new CipherValue('GMhpk09X+quNC/SsnxcDglZU/DCLAu9bMJ5bPcgaBK4s3F1eXciU8hlOYNaskSwP86HmA704NbzSDOHAgN6ckR+iCssxA7XCBjz0hltsgfn5p9Rh8qKtKltiXvxo/xXTcSXXZXNcE0R2KTya0P4DjZvYYgbIls/AH8ZyDV07ntI=')),
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    new EncryptionMethod('http://www.w3.org/2009/xmlenc11#rsa-oaep'),
                ),
            ]),
        );
        $ek = new EncryptedKey(
            new CipherData(new CipherValue('GMhpk09X+quNC/SsnxcDglZU/DCLAu9bMJ5bPcgaBK4s3F1eXciU8hlOYNaskSwP86HmA704NbzSDOHAgN6ckR+iCssxA7XCBjz0hltsgfn5p9Rh8qKtKltiXvxo/xXTcSXXZXNcE0R2KTya0P4DjZvYYgbIls/AH8ZyDV07ntI=')),
            'Encrypted_KEY_ID',
            null,
            null,
            null,
            'some_ENTITY_ID',
            new CarriedKeyName('Name of the key'),
            new EncryptionMethod('http://www.w3.org/2001/04/xmlenc#rsa-1_5'),
            null,
            new ReferenceList(
                [new DataReference('#Encrypted_DATA_ID')]
            )
        );
        $eid = new EncryptedID($ed, [$ek]);
        $eidElement = $eid->toXML();

        // Test for an EncryptedID
        $xpCache = XPath::getXPath($eidElement);
        $eidElements = XPath::xpQuery($eidElement, './xenc:EncryptedData', $xpCache);
        $this->assertCount(1, $eidElements);

        // Note: this cannot be tested as long as we don't include EncryptedKey in the structure
        // (see simplesamlphp/xml-security issue #30)
        //
        // Test ordering of EncryptedID contents
        /** @psalm-var \DOMElement[] $eidElements */
        $eidElements = XPath::xpQuery($eidElement, './xenc:EncryptedData/following-sibling::*', $xpCache);
        $this->assertCount(1, $eidElements);
        $this->assertEquals('xenc:EncryptedKey', $eidElements[0]->tagName);
    }


    /**
     * Test encryption / decryption
     */
    public function testEncryption(): void
    {
        $container = ContainerSingleton::getInstance(new MockContainer());
        $container->setBlacklistedAlgorithms(null);

        // Create keys
        $privKey = PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY);
        $pubKey = PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY);

        // Create encryptor
        $encryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            C::KEY_TRANSPORT_OAEP,
            $pubKey,
        );

        // test with a NameID
        $nameid = new NameID('value', 'name_qualifier');
        $encid = new EncryptedID($nameid->encrypt($encryptor));
        $doc = DOMDocumentFactory::fromString(strval($encid));

        $encid = EncryptedID::fromXML($doc->documentElement);
        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encid->getEncryptedKey()->getEncryptionMethod()->getAlgorithm(),
            $privKey
        );
        $id = $encid->decrypt($decryptor);
        $this->assertEquals(strval($nameid), strval($id));

        // test with Issuer
        $issuer = new Issuer('entityID');
        $encid = new EncryptedID($issuer->encrypt($encryptor));
        $doc = DOMDocumentFactory::fromString(strval($encid));

        $encid = EncryptedID::fromXML($doc->documentElement);
        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encid->getEncryptedKey()->getEncryptionMethod()->getAlgorithm(),
            $privKey
        );
        $id = $encid->decrypt($decryptor);
        $this->assertInstanceOf(Issuer::class, $id);
        $this->assertEquals(strval($issuer), strval($id));

        // test a custom BaseID without registering it
        $customid = new CustomBaseID(1.0, 'name_qualifier');
        $encid = new EncryptedID($customid->encrypt($encryptor));
        $doc = DOMDocumentFactory::fromString(strval($encid));

        $encid = EncryptedID::fromXML($doc->documentElement);
        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encid->getEncryptedKey()->getEncryptionMethod()->getAlgorithm(),
            $privKey
        );
        $id = $encid->decrypt($decryptor);
        $this->assertInstanceOf(BaseID::class, $id);
        $this->assertEquals(strval($customid), strval($id));

        // test a custom BaseID with a registered handler
        $container = $this->createMock(MockContainer::class);
        $container->method('getIdentifierHandler')->willReturn(CustomBaseID::class);
        ContainerSingleton::setContainer($container);

        $encid = EncryptedID::fromXML($doc->documentElement);
        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encid->getEncryptedKey()->getEncryptionMethod()->getAlgorithm(),
            $privKey
        );
        $id = $encid->decrypt($decryptor);
        $this->assertInstanceOf(CustomBaseID::class, $id);
        $this->assertEquals(strval($customid), strval($id));

        // test with unsupported ID
        $attr = new Attribute('name');
        $encid = new EncryptedID($attr->encrypt($encryptor));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown or unsupported encrypted identifier.');
        $encid->decrypt($decryptor);
    }
}
