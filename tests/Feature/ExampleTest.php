<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test redirect dari root ke /peminjaman.
     */
    public function test_the_application_redirects_to_peminjaman(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/peminjaman');
    }
}
