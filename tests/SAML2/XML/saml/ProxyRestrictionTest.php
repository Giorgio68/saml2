<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\ProxyRestriction;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\ProxyRestrictionTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\ProxyRestriction
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class ProxyRestrictionTest extends TestCase
{
    use SerializableElementTestTrait;


    /**
     */
    public function setup(): void
    {
        $this->testedClass = ProxyRestriction::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_ProxyRestriction.xml'
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $condition = new ProxyRestriction(
            [
                new Audience('urn:test:audience1'),
                new Audience('urn:test:audience2')
            ],
            2
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($condition)
        );
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $condition = ProxyRestriction::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($condition)
        );
    }
}
