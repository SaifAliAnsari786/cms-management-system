<?php

namespace Tests\Feature;

use OpenApi\Analysers\ReflectionAnalyser;
use Tests\TestCase;

class SwaggerConfigTest extends TestCase
{
    public function test_l5_swagger_uses_a_docblock_capable_analyser(): void
    {
        $analyser = config('l5-swagger.defaults.scanOptions.analyser');

        $this->assertInstanceOf(ReflectionAnalyser::class, $analyser);
    }
}
