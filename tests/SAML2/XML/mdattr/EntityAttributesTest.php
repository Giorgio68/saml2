<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdattr;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeStatement;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\saml\AuthnContext;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\AudienceRestriction;
use SimpleSAML\SAML2\XML\saml\Conditions;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\mdattr\EntityAttributes;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\mdattr\EntityAttributesTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdattr\EntityAttributes
 * @covers \SimpleSAML\SAML2\XML\mdattr\AbstractMdattrElement
 * @package simplesamlphp/saml2
 */

final class EntityAttributesTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->testedClass = EntityAttributes::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdattr_EntityAttributes.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $attribute1 = new Attribute(
            'attrib1',
            C::NAMEFORMAT_URI,
            null,
            [
                new AttributeValue('is'),
                new AttributeValue('really'),
                new AttributeValue('cool'),
            ]
        );

        // Create an Issuer
        $issuer = new Issuer('testIssuer');

        // Create the conditions
        $conditions = new Conditions(
            null,
            null,
            [],
            [new AudienceRestriction([new Audience('audience1'), new Audience('audience2')])]
        );

        // Create the statements
        $attrStatement = new AttributeStatement(
            [
                new Attribute(
                    'urn:mace:dir:attribute-def:uid',
                    C::NAMEFORMAT_URI,
                    null,
                    [
                        new AttributeValue('student2')
                    ]
                ),
                new Attribute(
                    'urn:mace:terena.org:attribute-def:schacHomeOrganization',
                    C::NAMEFORMAT_URI,
                    null,
                    [
                        new AttributeValue('university.example.org'),
                        new AttributeValue('bbb.cc')
                    ]
                ),
                new Attribute(
                    'urn:schac:attribute-def:schacPersonalUniqueCode',
                    C::NAMEFORMAT_URI,
                    null,
                    [
                        new AttributeValue('urn:schac:personalUniqueCode:nl:local:uvt.nl:memberid:524020'),
                        new AttributeValue('urn:schac:personalUniqueCode:nl:local:surfnet.nl:studentid:12345')
                    ]
                ),
                new Attribute(
                    'urn:mace:dir:attribute-def:eduPersonAffiliation',
                    C::NAMEFORMAT_URI,
                    null,
                    [
                        new AttributeValue('member'),
                        new AttributeValue('student')
                    ]
                )
            ]
        );

        $subject = new Subject(new NameID('some:entity', null, null, C::NAMEID_ENTITY));

        // Create an assertion
        $unsignedAssertion = new Assertion($issuer, null, 1610743797, $subject, $conditions, [$attrStatement]);

        // Sign the assertion
        $key = PrivateKey::fromFile(
            'vendor/simplesamlphp/xml-security'
            . PEMCertificatesMock::CERTIFICATE_DIR_RSA
            . '/'
            . PEMCertificatesMock::PRIVATE_KEY
        );
        $signer = (new SignatureAlgorithmFactory())->getAlgorithm(
            C::SIG_RSA_SHA256,
            $key
        );
        $unsignedAssertion->sign($signer);
        $signedAssertion = Assertion::fromXML($unsignedAssertion->toXML());

        $attribute2 = new Attribute(
            'foo',
            'urn:simplesamlphp:v1:simplesamlphp',
            null,
            [
                new AttributeValue('is'),
                new AttributeValue('really'),
                new AttributeValue('cool')
            ]
        );

        $entityAttributes = new EntityAttributes([$attribute1]);
        $entityAttributes->addChild($signedAssertion);
        $entityAttributes->addChild($attribute2);

        $this->assertEquals($this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement), strval($entityAttributes));
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $entityAttributes = EntityAttributes::fromXML($this->xmlRepresentation->documentElement);
        $this->assertCount(3, $entityAttributes->getChildren());

        $this->assertInstanceOf(Attribute::class, $entityAttributes->getChildren()[0]);
        $this->assertInstanceOf(Assertion::class, $entityAttributes->getChildren()[1]);
        $this->assertInstanceOf(Attribute::class, $entityAttributes->getChildren()[2]);

        $this->assertEquals('attrib1', $entityAttributes->getChildren()[0]->getName());
        $this->assertEquals(
            C::NAMEFORMAT_URI,
            $entityAttributes->getChildren()[0]->getNameFormat()
        );
        $this->assertCount(3, $entityAttributes->getChildren()[0]->getAttributeValues());


        $this->assertEquals('Assertion', $entityAttributes->getChildren()[1]->getLocalName());
        $this->assertEquals(
            '1984-08-26T10:01:30.000Z',
            $entityAttributes->getChildren()[1]->getXML()->getAttribute('IssueInstant')
        );

        $this->assertEquals('urn:simplesamlphp:v1:simplesamlphp', $entityAttributes->getChildren()[2]->getName());
        $this->assertEquals(
            C::NAMEFORMAT_URI,
            $entityAttributes->getChildren()[2]->getNameFormat()
        );
    }
}

