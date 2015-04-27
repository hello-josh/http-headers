<?php
/**
 * Copyright 2015 Joshua Johnston.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  * Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 *
 *  * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  * Neither the name of the Avant Web Consulting nor the names of its contributors
 *   may be used to endorse or promote products derived from this software without specific
 *   prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   HTTPHeaders
 * @package    HTTPHeaders
 * @subpackage Tests
 * @author     Joshua Johnston <johnston.joshua@gmail.com>
 * @copyright  2015 Joshua Johnston
 * @license    http://opensource.org/licenses/BSD-3-Clause BSD 3 Clause
 */

namespace Trii\HTTPHeaders\Tests;

use Trii\HTTPHeaders;

/**
 * Dummy class to test the abstract class HTTPHeaders\Header
 */
class TheHeader extends HTTPHeaders\Header {
    /**
     * Header name
     * @return string
     */
    public function getName() {
        return 'The-Header';
    }
}

/**
 * Tests the abstract class by way of TheHeader subclass
 */
class HeaderTest extends \PHPUnit_Framework_TestCase {

    public function testConstructor() {
        $header = new TheHeader();
        $this->assertEquals(null, $header->getValue());
    }

    public function testConstructorWithValue() {
        $header = new TheHeader("josh");
        $this->assertEquals("josh", $header->getValue());
    }

    public function testSetValue() {
        $header = new TheHeader();
        $header->setValue("josh");
        $this->assertEquals("josh", $header->getValue());
    }

    public function testGetValue() {
        $header = new TheHeader();
        $this->assertEquals(null, $header->getValue());
    }

    public function testParse() {
        $header = new TheHeader();
        $header->parse("josh");
        $this->assertEquals("josh", $header->getValue());
    }
}

/**
 * Tests the Accept Header against the RFC
 *
 * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.1
 */
class AcceptTest extends \PHPUnit_Framework_TestCase {

    /**
     * Make sure a single accept header is parsed properly
     */
    public function testParseSingleTypeSimple() {
        // basic mime
        $accept = new HTTPHeaders\Accept('text/html');
        $this->assertEquals(['text/html'], iterator_to_array($accept, false));
    }

    public function testParseSingleTypWithQuality() {
        // mime with a quality flag
        $accept = new HTTPHeaders\Accept('text/html; q=0.2');

        $this->assertEquals(['text/html'], iterator_to_array($accept, false));

        // mime with an extension parameter named level
        // with a value of 1
        $accept = new HTTPHeaders\Accept('text/html;level=1');

        $this->assertEquals(['text/html;level=1'], iterator_to_array($accept, false));

    }

    public function testParseSingleTypWithExtensionAndQuality() {
        // mime with an extension parameter of josh with a value
        // of hello and a quality of 0.1
        $accept = new HTTPHeaders\Accept('text/html;josh="hello";q=0.1');

        $this->assertEquals(['text/html;josh="hello"'], iterator_to_array($accept, false));

        // mime with an extension parameter of josh with a value
        // of hello and a quality of 0.1 but space separated
        $accept = new HTTPHeaders\Accept('text/html;josh="hello"; q=0.1');

        $this->assertEquals(['text/html;josh="hello"'], iterator_to_array($accept, false));
    }

    /**
     * Test accept headers with multiple items parse properly
     */
    public function testParseMultipleTypes() {
        $accept = new HTTPHeaders\Accept('text/html, text/xml;level=1, text/*');

        $this->assertEquals(
            ['text/html', 'text/xml;level=1', 'text/*'],
            iterator_to_array($accept, false)
        );

        $accept = new HTTPHeaders\Accept('audio/*; q=0.2, audio/basic');

        $this->assertEquals(
            ['audio/basic', 'audio/*'], iterator_to_array($accept, false)
        );

        $accept = new HTTPHeaders\Accept('text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c');

        $this->assertEquals(
            ['text/html',  'text/x-c', 'text/x-dvi', 'text/plain'],
            iterator_to_array($accept, false)
        );

        $accept = new HTTPHeaders\Accept('text/*, text/html, text/html;level=1, */*');

        $this->assertEquals(
            ['text/html;level=1', 'text/html', 'text/*', '*/*' ],
            iterator_to_array($accept, false)
        );

        $accept = new HTTPHeaders\Accept('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,application/json');

        $this->assertEquals(
            ['text/html', 'application/xhtml+xml', 'application/json', 'application/xml', '*/*' ],
            iterator_to_array($accept, false)
        );
    }

    /**
     * Tests that the preferred type is always first
     */
    public function testGetPreferredType() {
        $accept = new HTTPHeaders\Accept('audio/*; q=0.2, audio/basic');

        $preferredType = $accept->getPreferredType();

        $this->assertEquals('audio/basic', $preferredType);

        $accept = new HTTPHeaders\Accept('text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c');

        $preferredType = $accept->getPreferredType();

        $this->assertEquals('text/html', $preferredType);

        $accept = new HTTPHeaders\Accept('text/*, text/html, text/html;level=1, */*');

        $preferredType = $accept->getPreferredType();

        $this->assertEquals('text/html;level=1', $preferredType);
    }

    /**
     * Tests that mime types are found or not
     */
    public function testIsAccepted() {
        $accept = new HTTPHeaders\Accept('audio/*; q=0.2, audio/basic');

        $this->assertTrue(
            $accept->isAccepted('audio/basic'), 'Exact match failed'
        );

        $this->assertTrue(
            $accept->isAccepted('audio/mpeg'), 'Wildcard audio did not match'
        );

        $this->assertFalse(
            $accept->isAccepted('video/mpeg'), 'Wrong type matched'
        );

        $accept = new HTTPHeaders\Accept('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,application/json');

        $this->assertTrue(
            $accept->isAccepted('application/xhtml+xml'), 'Did not accept application/xhtml+xml'
        );

        $this->assertTrue(
            $accept->isAccepted('application/json'), 'Did not accept application/json'
        );
    }

}

/**
 * Tests the Accept-Charset Header against the RFC
 *
 * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.2
 */
class AcceptCharsetTest extends \PHPUnit_Framework_TestCase {

    /**
     * Make sure a single accept-charset header is parsed properly
     */
    public function testParseSingleType() {
        // basic
        $acceptCharset = new HTTPHeaders\AcceptCharset('ISO-8859-1');

        $charsets = $acceptCharset->getCharsets();

        $this->assertEquals(['iso-8859-1'], array_values($charsets));

        // with a quality flag
        $acceptCharset = new HTTPHeaders\AcceptCharset('ISO-8859-1; q=0.2');

        $charsets = $acceptCharset->getCharsets();

        $this->assertEquals(['iso-8859-1'], array_values($charsets));

        // The special value "*", if present in the Accept-Charset field,
        // matches every character set (including ISO-8859-1) which is not
        // mentioned elsewhere in the Accept-Charset field. If no "*" is present
        // in an Accept-Charset field, then all character sets not explicitly
        // mentioned get a quality value of 0, except for ISO-8859-1, which gets
        // a quality value of 1 if not explicitly mentioned.
        $acceptCharset = new HTTPHeaders\AcceptCharset('UTF-8');

        $charsets = $acceptCharset->getCharsets();

        $this->assertEquals(['utf-8', 'iso-8859-1'], array_values($charsets));
    }

    /**
     * Test accept headers with multiple items parse properly
     *
     * The special value "*", if present in the Accept-Charset field,
     * matches every character set (including ISO-8859-1) which is not
     * mentioned elsewhere in the Accept-Charset field. If no "*" is present
     * in an Accept-Charset field, then all character sets not explicitly
     * mentioned get a quality value of 0, except for ISO-8859-1, which gets
     * a quality value of 1 if not explicitly mentioned.
     */
    public function testParseMultipleTypes() {
        $acceptCharset = new HTTPHeaders\AcceptCharset('iso-8859-5, unicode-1-1;q=0.8');

        $charsets = $acceptCharset->getCharsets();

        // no * so implies iso-8859-1
        $this->assertEquals(
            [
            'iso-8859-5',
            'iso-8859-1',
            'unicode-1-1'
            ], $charsets
        );

        $acceptCharset = new HTTPHeaders\AcceptCharset('UTF-8,*');

        $charsets = $acceptCharset->getCharsets();

        // has a * so no iso-8859-1
        $this->assertEquals(
            [
            'utf-8',
            '*'
            ], $charsets
        );
    }

    /**
     * Tests that the preferred type is always first
     */
    public function testGetPreferredCharset() {
        $acceptCharset = new HTTPHeaders\AcceptCharset('iso-8859-5, unicode-1-1;q=0.8');

        $preferred = $acceptCharset->getPreferredCharset();

        $this->assertEquals('iso-8859-5', $preferred);

        // kinda odd that none are q=1 but just in case . ...
        $acceptCharset = new HTTPHeaders\AcceptCharset('unicode-1-1;q=0.8');

        $preferred = $acceptCharset->getPreferredCharset();

        $this->assertEquals('iso-8859-1', $preferred);
    }

    /**
     * Tests that mime types are found or not
     */
    public function testIsAccepted() {
        $acceptCharset = new HTTPHeaders\AcceptCharset('*');

        $this->assertTrue(
            $acceptCharset->isAccepted('utf-8'), 'Did not accept UTF-8 when given a wildcard *');

        $acceptCharset = new HTTPHeaders\AcceptCharset('iso-8859-5, unicode-1-1;q=0.8');

        $this->assertTrue(
            $acceptCharset->isAccepted('unicode-1-1'), 'Did not accept unicode-1-1 when given as an accepted type');

        $this->assertTrue(
            $acceptCharset->isAccepted('iso-8859-5'), 'Did not accept iso-8859-5 when given as an accepted type');

        $this->assertTrue(
            $acceptCharset->isAccepted('iso-8859-1'), 'Did not accept iso-8859-1 when not explicitly forbidden');
    }

}

/**
 * AcceptEncodingTest - Tests the Accept-Encoding header behaves as documented
 * in RFC 2616 Section 14
 *
 * This test will verify based off the RFC examples:
 * <ul>
 * <li>Accept-Encoding:</li>
 * <li>Accept-Encoding: *</li>
 * <li>Accept-Encoding: gzip</li>
 * <li>Accept-Encoding: compress, gzip</li>
 * <li>Accept-Encoding: compress;q=0.5, gzip;q=1.0</li>
 * <li>Accept-Encoding: gzip;q=1.0, identity; q=0.5, *;q=0</li>
 * </ul>
 *
 * Valid Encodings are:
 * <dl>
 * <dt>gzip (x-gzip)</dt>
 * <dd>An encoding format produced by the file compression program "gzip"
 * (GNU zip) as described in RFC 1952 [25]. This format is a Lempel-Ziv coding
 * (LZ77) with a 32 bit CRC.</dd>
 *
 * <dt>compress (x-compress)</dt>
 * <dd>The encoding format produced by the common UNIX file compression
 * program "compress". This format is an adaptive Lempel-Ziv-Welch coding (LZW).
 *
 *      <blockquote>
 *      Use of program names for the identification of encoding formats
 *      is not desirable and is discouraged for future encodings. Their
 *      use here is representative of historical practice, not good
 *      design. For compatibility with previous implementations of HTTP,
 *      applications SHOULD consider "x-gzip" and "x-compress" to be
 *      equivalent to "gzip" and "compress" respectively.
 *      </blockquote>
 * </dd>
 *
 * <dt>deflate</dt>
 * <dd>The "zlib" format defined in RFC 1950 [31] in combination with the
 * "deflate" compression mechanism described in RFC 1951 [29].</dd>
 *
 * <dt>identity</dt>
 * <dd>The default (identity) encoding; the use of no transformation
 * whatsoever. This content-coding is used only in the Accept- Encoding header,
 * and SHOULD NOT be used in the Content-Encoding header.</dd>
 * </dl>
 *
 * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.1
 * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec3.html#sec3.5 RFC 2616 Sec 3.5
 */
class AcceptEncodingTest extends \PHPUnit_Framework_TestCase {

    /**
     * Make sure a single Accept-Encoding header is parsed properly
     *
     * If the content-coding is one of the content-codings listed in
     * the Accept-Encoding field, then it is acceptable, unless it is
     * accompanied by a qvalue of 0. (As defined in section 3.9, a
     * qvalue of 0 means "not acceptable.")
     */
    public function testAcceptsValidEncoding() {

        // Accept-Encoding: gzip
        $acceptEncoding = new HTTPHeaders\AcceptEncoding('gzip');

        $this->assertTrue(
            $acceptEncoding->isAccepted('gzip'), 'Did not accept gzip when gzip was explicitly mentioned'
        );

        // Accept-Encoding: compress, gzip
        $acceptEncoding = new HTTPHeaders\AcceptEncoding('compress, gzip');

        $this->assertTrue(
            $acceptEncoding->isAccepted('gzip'), 'Did not accept gzip when gzip was explicitly mentioned'
        );

        $this->assertTrue(
            $acceptEncoding->isAccepted('compress'), 'Did not accept compress when compress was explicitly mentioned'
        );

        $this->assertFalse(
            $acceptEncoding->isAccepted('deflate'), 'Accepted deflate when deflate was not provided'
        );

        // Accept-Encoding: compress;q=0.5, gzip;q=1.0
        $acceptEncoding = new HTTPHeaders\AcceptEncoding('compress;q=0.5, gzip;q=1.0');

        $this->assertTrue(
            $acceptEncoding->isAccepted('gzip'), 'Did not accept gzip when gzip was explicitly mentioned'
        );

        $this->assertTrue(
            $acceptEncoding->isAccepted('compress'), 'Did not accept compress when compress was explicitly mentioned'
        );

        $this->assertFalse(
            $acceptEncoding->isAccepted('deflate'), 'Accepted deflate when deflate was not provided'
        );

        // Accept-Encoding: gzip;q=1.0, identity; q=0.5, deflate;q=0
        $acceptEncoding = new HTTPHeaders\AcceptEncoding('gzip;q=1.0, identity; q=0.5, deflate;q=0');

        $this->assertTrue(
            $acceptEncoding->isAccepted('gzip'), 'Did not accept gzip when gzip was explicitly mentioned'
        );

        $this->assertFalse(
            $acceptEncoding->isAccepted('deflate'), 'Accepted deflate when deflate had a quality of 0'
        );
    }

    /**
     * The special "*" symbol in an Accept-Encoding field matches any
     * available content-coding not explicitly listed in the header field.
     */
    public function testAcceptsWithWildcard() {
        // Accept-Encoding: *
        $acceptEncoding = new HTTPHeaders\AcceptEncoding('*');

        $this->assertTrue(
            $acceptEncoding->isAccepted('gzip'), 'Did not accept gzip when a wildcard was given'
        );

        // Accept-Encoding: *;q=0
        // should never happen in real life or else we can never send anything!
        $acceptEncoding = new HTTPHeaders\AcceptEncoding('*;q=0');

        $this->assertFalse(
            $acceptEncoding->isAccepted('deflate'), 'Accepted deflate when deflate was not mentioned and wildcard had a quality of 0'
        );

        // Accept-Encoding: gzip;q=1.0, identity; q=0.5, *;q=0
        $acceptEncoding = new HTTPHeaders\AcceptEncoding('gzip;q=1.0, identity; q=0.5, *;q=0');

        $this->assertFalse(
            $acceptEncoding->isAccepted('deflate'), 'Accepted deflate when deflate was not mentioned and wildcard had a quality of 0'
        );
    }

    /**
     * If multiple content-codings are acceptable, then the acceptable
     * content-coding with the highest non-zero qvalue is preferred.
     */
    public function testPreferredHighestNonZeroQuality() {
        // Accept-Encoding: compress;q=0.5, gzip;q=1.0
        $acceptEncoding = new HTTPHeaders\AcceptEncoding('compress;q=0.5, gzip;q=1.0');

        $this->assertEquals(
            'gzip', $acceptEncoding->getPreferredEncoding()
        );

        // Accept-Encoding: gzip;q=1.0, identity; q=0.5, *;q=0
        $acceptEncoding = new HTTPHeaders\AcceptEncoding('gzip;q=1.0, identity; q=0.5, *;q=0');

        $this->assertEquals(
            'gzip', $acceptEncoding->getPreferredEncoding()
        );

        // Accept-Encoding: *;q=0
        // should never happen in real life or else we can never send anything!
        $acceptEncoding = new HTTPHeaders\AcceptEncoding('*;q=0');

        $this->assertEmpty(
            $acceptEncoding->getPreferredEncoding()
        );

        // Accept-Encoding:
        $acceptEncoding = new HTTPHeaders\AcceptEncoding();

        $this->assertEquals(
            'identity', $acceptEncoding->getPreferredEncoding(), print_r($acceptEncoding, 1)
        );
    }

    /**
     * The "identity" content-coding is always acceptable, unless
     * specifically refused because the Accept-Encoding field includes
     * "identity;q=0", or because the field includes "*;q=0" and does
     * not explicitly include the "identity" content-coding. If the
     * Accept-Encoding field-value is empty, then only the "identity"
     * encoding is acceptable.
     */
    public function testIdentityAlwaysValidUnlessPassedQualityZero() {
        // Accept-Encoding:
        $acceptEncoding = new HTTPHeaders\AcceptEncoding('');

        $this->assertTrue(
            $acceptEncoding->isAccepted('identity'), 'Refused identity when given an empty list'
        );

        // Accept-Encoding: *
        $acceptEncoding = new HTTPHeaders\AcceptEncoding('*');

        $this->assertTrue(
            $acceptEncoding->isAccepted('identity'), 'Refused identity when given a wildcard with no qvalue'
        );

        // Accept-Encoding: gzip
        $acceptEncoding = new HTTPHeaders\AcceptEncoding('gzip');

        $this->assertTrue(
            $acceptEncoding->isAccepted('identity'), 'Refused identity when given just gzip'
        );

        // Accept-Encoding: compress, gzip
        $acceptEncoding = new HTTPHeaders\AcceptEncoding('compress, gzip');

        $this->assertTrue(
            $acceptEncoding->isAccepted('identity'), 'Refused identity when given two encodings but no wildcard or identity'
        );

        // Accept-Encoding: compress;q=0.5, gzip;q=1.0
        $acceptEncoding = new HTTPHeaders\AcceptEncoding('compress;q=0.5, gzip;q=1.0');

        $this->assertTrue(
            $acceptEncoding->isAccepted('identity'), 'Refused identity when given two encodings with qvalues but no wildcard or identity'
        );

        // Accept-Encoding: gzip;q=1.0, identity; q=0.5, *;q=0
        $acceptEncoding = new HTTPHeaders\AcceptEncoding('gzip;q=1.0, identity; q=0.5, *;q=0');

        $this->assertTrue(
            $acceptEncoding->isAccepted('identity'), 'Refused identity when given a wildcard with a 0 qvalue but identity was specified'
        );

        // Accept-Encoding: gzip;q=1.0, *;q=0
        $acceptEncoding = new HTTPHeaders\AcceptEncoding('gzip;q=1.0, *;q=0');

        $this->assertFalse(
            $acceptEncoding->isAccepted('identity'), 'Accepted identity when given a wildcard with a 0 qvalue and identity WAS NOT specified'
        );
    }

}
