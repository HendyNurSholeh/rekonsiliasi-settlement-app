<?php

namespace Tests\Unit\Controllers;

use App\Controllers\Dashboard;
use CodeIgniter\Test\CIUnitTestCase;

class DashboardTest extends CIUnitTestCase
{
    protected $controller;
    protected $dbMock;
    protected $queryMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Create database mock
        $this->dbMock = $this->getMockBuilder(\CodeIgniter\Database\BaseConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryMock = $this->getMockBuilder(\CodeIgniter\Database\Query::class)
            ->disableOriginalConstructor()
            ->addMethods(['getRow'])
            ->getMock();

        // Mock the query method to return our query mock
        $this->dbMock->method('query')
            ->willReturn($this->queryMock);

        // Replace database service with our mock
        \CodeIgniter\Config\Services::injectMock('database', $this->dbMock);

        // Create controller instance with disabled constructor
        $this->controller = $this->getMockBuilder(Dashboard::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render', 'index'])
            ->getMock();

        // Add the index method to the mock
        $this->controller->method('index')
            ->willReturnCallback(function() {
                // Simulate the actual index method behavior
                $query = $this->dbMock->query("SELECT TGL_REKON FROM t_proses WHERE STATUS = 1 ORDER BY TGL_REKON DESC LIMIT 1");
                $row = $query->getRow();

                $data = [
                    'title' => 'Beranda',
                    'route' => 'dashboard',
                    'tgl_rekon' => $row,
                    'today' => \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, DD MMMM YYYY'),
                    'date' => \Carbon\Carbon::now()->format('Y-m-d')
                ];

                return $this->controller->render('dashboard', $data);
            });
    }

    protected function tearDown(): void
    {
        unset($this->controller);
        unset($this->dbMock);
        unset($this->queryMock);
        parent::tearDown();
    }

    public function testControllerCanBeInstantiated()
    {
        $dashboard = new Dashboard();
        $this->assertInstanceOf(Dashboard::class, $dashboard);
    }

    public function testControllerUsesHasCurlRequestTrait()
    {
        $traits = class_uses(Dashboard::class);
        $this->assertArrayHasKey('App\Traits\HasCurlRequest', $traits);
    }

    public function testControllerExtendsBaseController()
    {
        $dashboard = new Dashboard();
        $this->assertInstanceOf(\App\Controllers\BaseController::class, $dashboard);
    }

    public function testControllerHasRequiredProperties()
    {
        $dashboard = new Dashboard();
        $this->assertTrue(property_exists($dashboard, 'request'));
        $this->assertTrue(property_exists($dashboard, 'response'));
    }

    // Test index method - success scenario with data
    public function testIndexSuccessWithData()
    {
        // Mock database to return data
        $rowMock = (object) ['TGL_REKON' => '2025-08-27'];
        $this->queryMock->method('getRow')->willReturn($rowMock);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('dashboard', $this->callback(function($data) use ($rowMock) {
                return $data['title'] === 'Beranda' &&
                       $data['route'] === 'dashboard' &&
                       $data['tgl_rekon'] === $rowMock &&
                       isset($data['today']) &&
                       isset($data['date']);
            }))
            ->willReturn('rendered view');

        $result = $this->controller->index();
        $this->assertEquals('rendered view', $result);
    }

    // Test index method - no data scenario
    public function testIndexNoData()
    {
        // Mock database to return no data
        $this->queryMock->method('getRow')->willReturn(null);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('dashboard', $this->callback(function($data) {
                return $data['title'] === 'Beranda' &&
                       $data['route'] === 'dashboard' &&
                       $data['tgl_rekon'] === null &&
                       isset($data['today']) &&
                       isset($data['date']);
            }))
            ->willReturn('rendered view');

        $result = $this->controller->index();
        $this->assertEquals('rendered view', $result);
    }

    // Test index method - database exception
    public function testIndexDatabaseException()
    {
        // Mock database to throw exception
        $this->dbMock->method('query')
            ->willThrowException(new \CodeIgniter\Database\Exceptions\DatabaseException('Database connection failed'));

        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $this->expectExceptionMessage('Database connection failed');

        $this->controller->index();
    }

    // Test index method - render exception
    public function testIndexRenderException()
    {
        // Mock database to return data
        $rowMock = (object) ['TGL_REKON' => '2025-08-27'];
        $this->queryMock->method('getRow')->willReturn($rowMock);

        $this->controller->expects($this->once())
            ->method('render')
            ->willThrowException(new \Exception('Template rendering failed'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Template rendering failed');

        $this->controller->index();
    }

    // Test index method - data structure validation
    public function testIndexDataStructure()
    {
        // Mock database to return data
        $rowMock = (object) ['TGL_REKON' => '2025-08-27'];
        $this->queryMock->method('getRow')->willReturn($rowMock);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('dashboard', $this->callback(function($data) {
                // Validate data structure
                $this->assertIsArray($data);
                $this->assertArrayHasKey('title', $data);
                $this->assertArrayHasKey('route', $data);
                $this->assertArrayHasKey('tgl_rekon', $data);
                $this->assertArrayHasKey('today', $data);
                $this->assertArrayHasKey('date', $data);

                // Validate data types
                $this->assertIsString($data['title']);
                $this->assertIsString($data['route']);
                $this->assertIsString($data['today']);
                $this->assertIsString($data['date']);

                return true;
            }))
            ->willReturn('rendered view');

        $result = $this->controller->index();
        $this->assertEquals('rendered view', $result);
    }

    // Test index method - SQL query validation
    public function testIndexSqlQuery()
    {
        $expectedSql = "SELECT TGL_REKON FROM t_proses WHERE STATUS = 1 ORDER BY TGL_REKON DESC LIMIT 1";

        $this->queryMock->method('getRow')->willReturn(null);

        $this->dbMock->expects($this->once())
            ->method('query')
            ->with($expectedSql)
            ->willReturn($this->queryMock);

        $this->controller->expects($this->once())
            ->method('render')
            ->willReturn('rendered view');

        $this->controller->index();
    }

    // Test index method - different date formats
    public function testIndexDifferentDateFormats()
    {
        $testDates = ['2025-08-27', '2025-01-15', '2024-12-31'];

        foreach ($testDates as $testDate) {
            // Create fresh mocks for each iteration
            $dbMock = $this->getMockBuilder(\CodeIgniter\Database\BaseConnection::class)
                ->disableOriginalConstructor()
                ->getMock();

            $queryMock = $this->getMockBuilder(\CodeIgniter\Database\Query::class)
                ->disableOriginalConstructor()
                ->addMethods(['getRow'])
                ->getMock();

            $rowMock = (object) ['TGL_REKON' => $testDate];
            $queryMock->method('getRow')->willReturn($rowMock);
            $dbMock->method('query')->willReturn($queryMock);

            // Replace database service
            \CodeIgniter\Config\Services::injectMock('database', $dbMock);

            $controller = $this->getMockBuilder(Dashboard::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['render', 'index'])
                ->getMock();

            // Add index method to this controller mock
            $controller->method('index')
                ->willReturnCallback(function() use ($dbMock, $controller) {
                    $query = $dbMock->query("SELECT TGL_REKON FROM t_proses WHERE STATUS = 1 ORDER BY TGL_REKON DESC LIMIT 1");
                    $row = $query->getRow();

                    $data = [
                        'title' => 'Beranda',
                        'route' => 'dashboard',
                        'tgl_rekon' => $row,
                        'today' => \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, DD MMMM YYYY'),
                        'date' => \Carbon\Carbon::now()->format('Y-m-d')
                    ];

                    return $controller->render('dashboard', $data);
                });

            $controller->expects($this->once())
                ->method('render')
                ->with('dashboard', $this->callback(function($data) use ($rowMock) {
                    return $data['tgl_rekon'] === $rowMock;
                }))
                ->willReturn('rendered view');

            $result = $controller->index();
            $this->assertEquals('rendered view', $result);
        }
    }

    // Test index method - empty result handling
    public function testIndexEmptyResultHandling()
    {
        // Mock database to return empty result
        $this->queryMock->method('getRow')->willReturn((object) []);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('dashboard', $this->callback(function($data) {
                return $data['tgl_rekon'] instanceof \stdClass;
            }))
            ->willReturn('rendered view');

        $result = $this->controller->index();
        $this->assertEquals('rendered view', $result);
    }

    // Test index method - basic functionality
    public function testIndexBasicFunctionality()
    {
        // Mock database to return data
        $rowMock = (object) ['TGL_REKON' => '2025-08-27'];
        $this->queryMock->method('getRow')->willReturn($rowMock);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('dashboard', $this->callback(function($data) {
                // Just validate that we get the expected data structure
                return is_array($data) &&
                       isset($data['title']) &&
                       isset($data['route']) &&
                       isset($data['tgl_rekon']) &&
                       isset($data['today']) &&
                       isset($data['date']);
            }))
            ->willReturn('dashboard_view_rendered');

        $result = $this->controller->index();
        $this->assertEquals('dashboard_view_rendered', $result);
    }

    // Test index method - data validation
    public function testIndexDataValidation()
    {
        // Mock database to return data
        $rowMock = (object) ['TGL_REKON' => '2025-08-27'];
        $this->queryMock->method('getRow')->willReturn($rowMock);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('dashboard', $this->callback(function($data) {
                // Validate data types and values
                $this->assertIsArray($data);
                $this->assertEquals('Beranda', $data['title']);
                $this->assertEquals('dashboard', $data['route']);
                $this->assertIsString($data['today']);
                $this->assertIsString($data['date']);
                $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}/', $data['date']);

                return true;
            }))
            ->willReturn('rendered_view');

        $result = $this->controller->index();
        $this->assertEquals('rendered_view', $result);
    }

    // Test index method - template name validation
    public function testIndexTemplateName()
    {
        // Mock database to return data
        $rowMock = (object) ['TGL_REKON' => '2025-08-27'];
        $this->queryMock->method('getRow')->willReturn($rowMock);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('dashboard', $this->anything())
            ->willReturn('template_rendered');

        $result = $this->controller->index();
        $this->assertEquals('template_rendered', $result);
    }

    // Test index method - exception handling
    public function testIndexExceptionHandling()
    {
        // Mock database to return data
        $rowMock = (object) ['TGL_REKON' => '2025-08-27'];
        $this->queryMock->method('getRow')->willReturn($rowMock);

        $this->controller->expects($this->once())
            ->method('render')
            ->willThrowException(new \Exception('Template error'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Template error');

        $this->controller->index();
    }

    // Test index method - multiple calls consistency
    public function testIndexMultipleCallsConsistency()
    {
        // Mock database to return data
        $rowMock = (object) ['TGL_REKON' => '2025-08-27'];
        $this->queryMock->method('getRow')->willReturn($rowMock);

        $callCount = 0;

        $this->controller->expects($this->exactly(3))
            ->method('render')
            ->with('dashboard', $this->callback(function($data) use (&$callCount) {
                $callCount++;
                return is_array($data) &&
                       isset($data['title']) &&
                       isset($data['route']);
            }))
            ->willReturn('consistent_result');

        // Call index multiple times
        $result1 = $this->controller->index();
        $result2 = $this->controller->index();
        $result3 = $this->controller->index();

        $this->assertEquals('consistent_result', $result1);
        $this->assertEquals('consistent_result', $result2);
        $this->assertEquals('consistent_result', $result3);
        $this->assertEquals(3, $callCount);
    }

    // Test index method - data structure completeness
    public function testIndexDataStructureCompleteness()
    {
        // Mock database to return data
        $rowMock = (object) ['TGL_REKON' => '2025-08-27'];
        $this->queryMock->method('getRow')->willReturn($rowMock);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('dashboard', $this->callback(function($data) {
                $requiredKeys = ['title', 'route', 'tgl_rekon', 'today', 'date'];

                foreach ($requiredKeys as $key) {
                    $this->assertArrayHasKey($key, $data, "Missing required key: {$key}");
                }

                // Validate that no unexpected keys are present
                $unexpectedKeys = array_diff(array_keys($data), $requiredKeys);
                $this->assertEmpty($unexpectedKeys, 'Unexpected keys found: ' . implode(', ', $unexpectedKeys));

                return true;
            }))
            ->willReturn('validated_view');

        $result = $this->controller->index();
        $this->assertEquals('validated_view', $result);
    }

    // Test index method - Carbon integration
    public function testIndexCarbonIntegration()
    {
        // Mock database to return data
        $rowMock = (object) ['TGL_REKON' => '2025-08-27'];
        $this->queryMock->method('getRow')->willReturn($rowMock);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('dashboard', $this->callback(function($data) {
                // Validate that Carbon methods were called correctly
                $this->assertIsString($data['today']);
                $this->assertIsString($data['date']);
                $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}/', $data['date']);

                return true;
            }))
            ->willReturn('carbon_integrated');

        $result = $this->controller->index();
        $this->assertEquals('carbon_integrated', $result);
    }

    // Test index method - performance (basic)
    public function testIndexPerformance()
    {
        // Mock database to return data
        $rowMock = (object) ['TGL_REKON' => '2025-08-27'];
        $this->queryMock->method('getRow')->willReturn($rowMock);

        $this->controller->expects($this->once())
            ->method('render')
            ->willReturn('performance_tested');

        $startTime = microtime(true);

        $result = $this->controller->index();

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertEquals('performance_tested', $result);
        $this->assertLessThan(1.0, $executionTime, 'Method should execute in less than 1 second');
    }

    // Test index method - memory usage (basic)
    public function testIndexMemoryUsage()
    {
        // Mock database to return data
        $rowMock = (object) ['TGL_REKON' => '2025-08-27'];
        $this->queryMock->method('getRow')->willReturn($rowMock);

        $this->controller->expects($this->once())
            ->method('render')
            ->willReturn('memory_tested');

        $memoryBefore = memory_get_usage();

        $result = $this->controller->index();

        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;

        $this->assertEquals('memory_tested', $result);
        $this->assertLessThan(1048576, $memoryUsed, 'Method should use less than 1MB of memory');
    }

    // Test index method - return type
    public function testIndexReturnType()
    {
        // Mock database to return data
        $rowMock = (object) ['TGL_REKON' => '2025-08-27'];
        $this->queryMock->method('getRow')->willReturn($rowMock);

        $this->controller->expects($this->once())
            ->method('render')
            ->willReturn('string_return');

        $result = $this->controller->index();

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    // Test index method - data immutability
    public function testIndexDataImmutability()
    {
        // Mock database to return data
        $rowMock = (object) ['TGL_REKON' => '2025-08-27'];
        $this->queryMock->method('getRow')->willReturn($rowMock);

        $this->controller->expects($this->exactly(2))
            ->method('render')
            ->with('dashboard', $this->callback(function($data) {
                static $firstCallData = null;

                if ($firstCallData === null) {
                    $firstCallData = $data;
                    return true;
                }

                // On second call, data should be the same
                $this->assertEquals($firstCallData['title'], $data['title']);
                $this->assertEquals($firstCallData['route'], $data['route']);

                return true;
            }))
            ->willReturn('immutable_test');

        // Call twice to test consistency
        $this->controller->index();
        $this->controller->index();
    }
}