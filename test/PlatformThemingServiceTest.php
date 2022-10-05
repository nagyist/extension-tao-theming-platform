<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\taoThemingPlatform\test;

use oat\tao\test\TaoPhpUnitTestRunner;
use oat\taoThemingPlatform\model\PlatformThemingService;
use oat\taoThemingPlatform\model\PlatformThemingConfig;

class PlatformThemingServiceTest extends TaoPhpUnitTestRunner
{
    private $service = null;
    private $tempConfig = null;
    
    public function tearDown(): void
    {
        parent::tearDown();
        
        // Restore previous Theming config...
        $this->service->syncThemingConfig($this->tempConfig);
        
        // Deal with data storage.
        $dataDir = $this->service->getDataDirectory();
        @unlink(rtrim($dataDir->getAbsolutePath(), "/\\") . '/data.txt');
        @unlink(rtrim(sys_get_temp_dir(), "\\/") . '/tmp-platformthemingtest.txt');
        @unlink(rtrim(sys_get_temp_dir(), "\\/") . '/tmp-mynewname.txt');
        
        unset($service);
    }
    
    public function setUp(): void
    {
        parent::setUp();
        
        $this->service = PlatformThemingService::singleton();
        
        // Save current Theming config...
        $this->tempConfig = $this->service->retrieveThemingConfig();
        
        // Set up all tests with an empty Theming Configuration.
        $this->service->syncThemingConfig(new PlatformThemingConfig());
        
        // Deal with data storage.
        $testFile = rtrim(sys_get_temp_dir(), "\\/") . '/tmp-platformthemingtest.txt';
        file_put_contents($testFile, 'data');
    }
    
    /**
     * Aims at testing that a proper empty theming configuration is set.
     */
    public function testEmptyRetrieveThemingConfig()
    {
        $conf = $this->service->retrieveThemingConfig();
        $this->assertEquals(0, count($conf));
    }
    
    public function testSyncThemingConfig()
    {
        $conf = $this->service->retrieveThemingConfig();
        $conf['key1'] = 'value1';
        
        $this->service->syncThemingConfig($conf);
        $conf = $this->service->retrieveThemingConfig();
        
        $this->assertEquals(1, count($conf));
        $this->assertEquals('value1', $conf['key1']);
    }
    
    /**
     * Aims at testing that the data directors is correctly configured.
     */
    public function testGetDataDirectory()
    {
        // should be data-source/assets.
        $dataDirectory = $this->service->getDataDirectory();
        $this->assertEquals('assets', $dataDirectory->getRelativePath());
    }
    
    /**
     * @depends testGetDataDirectory
     */
    public function testFileStorage()
    {
        $filePath = rtrim(sys_get_temp_dir(), "\\/") . '/tmp-platformthemingtest.txt';
        $finalPath = rtrim($this->service->getDataDirectory()->getAbsolutePath(), "\\/") . '/tmp-platformthemingtest.txt';
        $this->service->storeFile($filePath);
        $this->assertEquals('data', file_get_contents($finalPath));
        
        $finalPath = rtrim($this->service->getDataDirectory()->getAbsolutePath(), "\\/") . '/tmp-mynewname.txt';
        $this->service->storeFile($filePath, 'tmp-mynewname.txt');
        $this->assertEquals('data', file_get_contents($finalPath));
    }
}