<?php

namespace hipanel\tests\unit\components;

use hipanel\components\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = [];
        $_SERVER['REQUEST_METHOD'] = 'QUERY';
        unset($_POST['_method']);
    }

    protected function tearDown(): void
    {
        $_GET = [];
        unset($_SERVER['REQUEST_METHOD']);
        unset($_POST['_method']);
    }

    public function testQueryMethodMergesBodyOverUrlParams(): void
    {
        $_GET = ['foo' => 'from-url', 'onlyUrl' => '1'];

        $request = new Request();
        $request->setRawBody('foo=from-body&onlyBody=1');

        $params = $request->getQueryParams();

        $this->assertSame('from-body', $params['foo'], 'body value must override URL value on key collision');
        $this->assertSame('1', $params['onlyUrl'], 'URL-only keys must survive the merge');
        $this->assertSame('1', $params['onlyBody'], 'body-only keys must be present in the merge');
    }

    public function testQueryMethodIsCsrfSafeByDefault(): void
    {
        $request = new Request();

        $this->assertContains('QUERY', $request->csrfTokenSafeMethods);
        $this->assertTrue($request->validateCsrfToken('does-not-matter'));
    }

    public function testNonQueryMethodBehavesLikeParent(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['foo' => 'from-url'];

        $request = new Request();
        $request->setRawBody('foo=from-body&onlyBody=1');

        $this->assertSame(['foo' => 'from-url'], $request->getQueryParams());
    }
}
