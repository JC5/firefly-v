<?php

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-11-18 at 09:03:51.
 */
class HomeControllerTest extends TestCase
{
    /**
     * @var HomeController
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @covers HomeController::flush
     * @todo   Implement testFlush().
     */
    public function testFlush()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers HomeController::index
     * @todo   Implement testIndex().
     */
    public function testIndex()
    {
        $this->be(new User(['email' => 'test@example.com']));

        $response = $this->call('GET', '/');
        $this->assertResponseOk();

        $this->assertTrue(true);
    }

    /**
     * @covers HomeController::rangeJump
     * @todo   Implement testRangeJump().
     */
    public function testRangeJump()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers HomeController::sessionNext
     * @todo   Implement testSessionNext().
     */
    public function testSessionNext()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers HomeController::sessionPrev
     * @todo   Implement testSessionPrev().
     */
    public function testSessionPrev()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
}
