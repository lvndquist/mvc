<?php

namespace App\Entity;
use App\Entity\Library;
use App\Repository\LibraryRepository;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class Card.
 */
class EntityTest extends TestCase
{
    /**
     * Library object
     */
    public function testLibrary(): void
    {
        $test = new Library();
        $this->assertInstanceOf("\App\Entity\Library", $test);

        $title = "test";
        $author = "test";
        $isbn = "1234";
        $url = "test";

        $test->setTitle($title);
        $test->setAuthor($author);
        $test->setIsbn($isbn);
        $test->setImageUrl($url);

        $gotId = $test->getId();
        $gotTitle = $test->getTitle();
        $gotAuthor = $test->getAuthor();
        $gotIsbn = $test->getIsbn();
        $gotUrl = $test->getImageUrl();

        $this->assertEquals(null, $gotId);
        $this->assertEquals($title, $gotTitle);
        $this->assertEquals($author, $gotAuthor);
        $this->assertEquals($isbn, $gotIsbn);
        $this->assertEquals($url, $gotUrl);
    }
}
