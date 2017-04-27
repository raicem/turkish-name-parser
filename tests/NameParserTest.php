<?php

use PHPUnit\Framework\TestCase;

class NameParserTest extends TestCase
{
    protected $parser;

    public function setUp()
    {
        $this->parser = new Raicem\NameParser;
    }

    public function testParserAcceptsAName()
    {
        $this->parser->parse('John Doe');
        $this->assertEquals('John Doe', $this->parser->getRawName());
    }

    public function testParserCreatesAnArrayFromAName()
    {
        $this->parser->parse('John Doe');
        $this->assertEquals(['John', 'Doe'], $this->parser->getRawArray());
    }

    public function testParserConvertsToLowercase()
    {
        $string = $this->parser->convertToLowercase('İlhan');
        $this->assertEquals('ilhan', $string);

        $string = $this->parser->convertToLowercase('IRMAK');
        $this->assertEquals('ırmak', $string);

        $string = $this->parser->convertToLowercase('KAZIM');
        $this->assertEquals('kazım', $string);
    }

    public function testParserRemovesCharsAndNumbers()
    {
        $string = $this->parser->removeCharsAndNumbers('John+0');
        $this->assertEquals('John', $string);
    }

    public function testParserRemovesStartingRepeatingLetters()
    {
        $string = $this->parser->removeRepeatingStartingLetters('aahmet');
        $this->assertEquals('ahmet', $string);
    }

    public function testParsersChecksInvalidName()
    {
        $string = $this->parser->checkIfNameIsInvalid('sdfg');
        $this->assertFalse($string);
    }

    public function testParsersCapitilizesFirstLetter()
    {
        $string = $this->parser->capitalizeFirstLetter('cem');
        $this->assertEquals('Cem', $string);
    }

    public function testParserOrdersNames()
    {
        $this->parser->parse('John Doe');
        $expected = ['first_name' => 'John', 'last_name' => 'Doe'];
        $this->assertEquals($expected, $this->parser->asArray());
    }

    public function testParserRemovesRepeatedNames()
    {
        $this->parser->parse('Cem Cem Ünalan');
        $this->assertEquals(['first_name' => 'Cem', 'last_name' => 'Ünalan'], $this->parser->asArray());
    }

    public function testParserReturnsInvalidName()
    {
        $this->parser->parse('A.');
        $this->assertFalse($this->parser->isValid());
    }

    public function testParserReturnsInvalidChunks()
    {
        $this->parser->parse('Cem sdf Ünalan');
        $this->assertEquals(['sdf'], $this->parser->getInvalidChunks());
    }
}
