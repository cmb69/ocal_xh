<?php

/**
 * The environment variable CMSIMPLEDIR has to be set to the installation folder
 * (e.g. / or /cmsimple_xh/). There has to be a page "Ocal" with the calls
 * <code>ocal('test')</code> and <code>ocal_hourly('hourly')</code> on it.
 *
 * @copyright 2013-2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Ocal;

use PHPUnit_Framework_TestCase;

class CSRFAttackTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var resource
     */
    private $curlHandle;

    /**
     * @var string
     */
    private $cookieFile;

    public function setUp()
    {
        $this->url = 'http://localhost' . getenv('CMSIMPLEDIR');
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'CC');

        $this->curlHandle = curl_init($this->url . '?&login=true&keycut=test');
        curl_setopt($this->curlHandle, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_exec($this->curlHandle);
        curl_close($this->curlHandle);
    }

    /**
     * @param array $fields
     */
    private function setCurlOptions($fields)
    {
        $options = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile
        );
        curl_setopt_array($this->curlHandle, $options);
    }

    /**
     * @param string $queryString
     * @dataProvider dataForAttack
     */
    public function testAttack(array $fields, $queryString = null)
    {
        $url = $this->url . (isset($queryString) ? '?' . $queryString : '');
        $this->curlHandle = curl_init($url);
        $this->setCurlOptions($fields);
        curl_exec($this->curlHandle);
        $actual = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
        curl_close($this->curlHandle);
        $this->assertEquals(403, $actual);
    }

    /**
     * @return array
     */
    public function dataForAttack()
    {
        return array(
            array(
                array('dummy' => 'foo'),
                'Ocal&ocal_save=1&ocal_name=test&normal'
            ),
            array(
                array('dummy' => 'foo'),
                'Ocal&ocal_save=1&ocal_name=hourly&normal'
            )
        );
    }
}
