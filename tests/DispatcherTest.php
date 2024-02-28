<?php declare(strict_types=1);

use Kreme201\FileBaseRouter\Dispatcher;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class DispatcherTest extends TestCase
{
    private array $pages = [
        'pages' => [
            'index.php' => 'index',
            'board' => [
                '[id]' => [
                    'index.php' => 'board single',
                    'edit.php' => 'board edit',
                ],
                'index.php' => 'board_list',
            ],
            'post' => [
                '[...slug].php' => 'post_single',
                'index.php' => 'post_list',
            ],
        ],
    ];
    private string $base_path;
    private Dispatcher $dispatcher;

    public function test_Dispatch_Index()
    {
        $this->assertEquals($this->dispatcher->dispatch('/'), $this->base_path . '/index.php');
    }

    public function test_Dispatch_Board_List()
    {
        $this->assertEquals($this->dispatcher->dispatch('/board'), $this->base_path . '/board/index.php');
    }

    public function test_Dispatch_Board_Detail()
    {
        $this->assertEquals($this->dispatcher->dispatch('/board/123'), $this->base_path . '/board/[id]/index.php');
        $this->assertEquals(123, $_GET['id'] ?? '');
    }

    public function test_Dispatch_Board_Detail_Edit()
    {
        $this->assertEquals($this->dispatcher->dispatch('/board/456/edit'), $this->base_path . '/board/[id]/edit.php');
        $this->assertEquals(456, $_GET['id'] ?? '');
    }

    public function test_Dispatch_Post_List()
    {
        $this->assertEquals($this->dispatcher->dispatch('/post'), $this->base_path . '/post/index.php');
    }

    public function test_Dispatch_Post_Slug()
    {
        $this->assertEquals($this->dispatcher->dispatch('/post/test/slug'), $this->base_path . '/post/[...slug].php');
        $this->assertEquals('test/slug', $_GET['slug'] ?? '');
    }

    public function test_Dispatch_404()
    {
        $this->assertFalse($this->dispatcher->dispatch('/404'));
        $this->assertFalse($this->dispatcher->dispatch('/board/123/test'));
    }

    public function setUp(): void
    {
        $vfs = vfsStream::setup('root');
        vfsStream::create($this->pages, $vfs);

        $this->base_path = vfsStream::url('root/pages');
        $this->dispatcher = new Dispatcher($this->base_path);
    }
}
