<?php

declare(strict_types=1);

namespace SAML2\XML\xenc;

use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityDsig;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\Chunk;

/**
 * Class \SAML2\XML\xenc\DataReferenceTest
 *
 * @covers \SAML2\XML\xenc\AbstractReference
 * @covers \SAML2\XML\xenc\DataReference
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class DataReferenceTest extends TestCase
{
    /** @var \DOMDocument $document */
    private $document;

    /** @var \SAML2\XML\Chunk $reference */
    private $reference;


    /**
     * @return void
     */
    public function setup(): void
    {
        $xencNamespace = Constants::NS_XENC;
        $dsNamespace = XMLSecurityDSig::XMLDSIGNS;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<xenc:DataReference xmlns:xenc="{$xencNamespace}" URI="#Encrypted_DATA_ID">
  <ds:Transforms xmlns:ds="{$dsNamespace}">
    <ds:Transform Algorithm="http://www.w3.org/TR/1999/REC-xpath-19991116">
      <ds:XPath xmlns:xenc="http://www.w3.org/2001/04/xmlenc#">
        self::xenc:EncryptedData[@Id="example1"]
      </ds:XPath>
    </ds:Transform>
  </ds:Transforms>
</xenc:DataReference>
XML
        );

        $this->reference = new Chunk(DOMDocumentFactory::fromString(<<<XML
 <ds:Transforms xmlns:ds="{$dsNamespace}">
    <ds:Transform Algorithm="http://www.w3.org/TR/1999/REC-xpath-19991116">
      <ds:XPath xmlns:xenc="http://www.w3.org/2001/04/xmlenc#">
        self::xenc:EncryptedData[@Id="example1"]
      </ds:XPath>
    </ds:Transform>
  </ds:Transforms>
XML
        )->documentElement);
    }

    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $dataReference = new DataReference('#Encrypted_DATA_ID', [$this->reference]);

        $this->assertEquals('#Encrypted_DATA_ID', $dataReference->getURI());

        $references = $dataReference->getReferences();
        $this->assertCount(1, $references);
        $this->assertEquals($this->reference, $references[0]);

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($dataReference)
        );
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $dataReference = DataReference::fromXML($this->document->documentElement);

        $this->assertEquals('#Encrypted_DATA_ID', $dataReference->getURI());

        $references = $dataReference->getReferences();
        $this->assertCount(1, $references);
        $this->assertEquals($this->reference, $references[0]);

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($dataReference)
        );
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(DataReference::fromXML($this->document->documentElement))))
        );
    }
}