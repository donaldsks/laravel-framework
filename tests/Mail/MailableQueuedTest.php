<?php

namespace Illuminate\Tests\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\View\Factory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Foundation\Application;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Support\Testing\Fakes\QueueFake;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Swift_Mailer;

class MailableQueuedTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }

    public function testQueuedMailableSent()
    {
        $queueFake = new QueueFake(new Application);
        $mailer = $this->getMockBuilder(Mailer::class)
            ->setConstructorArgs($this->getMocks())
            ->onlyMethods(['createMessage', 'to'])
            ->getMock();
        $mailer->setQueue($queueFake);
        $mailable = new MailableQueableStub;
        $queueFake->assertNothingPushed();
        $mailer->send($mailable);
        $queueFake->assertPushedOn(null, SendQueuedMailable::class);
    }

    public function testQueuedMailableWithAttachmentSent()
    {
        $queueFake = new QueueFake(new Application);
        $mailer = $this->getMockBuilder(Mailer::class)
            ->setConstructorArgs($this->getMocks())
            ->onlyMethods(['createMessage'])
            ->getMock();
        $mailer->setQueue($queueFake);
        $mailable = new MailableQueableStub;
        $attachmentOption = ['mime' => 'image/jpeg', 'as' => 'bar.jpg'];
        $mailable->attach('foo.jpg', $attachmentOption);
        $this->assertIsArray($mailable->attachments);
        $this->assertCount(1, $mailable->attachments);
        $this->assertEquals($mailable->attachments[0]['options'], $attachmentOption);
        $queueFake->assertNothingPushed();
        $mailer->send($mailable);
        $queueFake->assertPushedOn(null, SendQueuedMailable::class);
    }

    public function testQueuedMailableWithAttachmentFromDiskSent()
    {
        $app = new Application;
        $container = Container::getInstance();
        $this->getMockBuilder(Filesystem::class)
            ->getMock();
        $filesystemFactory = $this->getMockBuilder(FilesystemManager::class)
            ->setConstructorArgs([$app])
            ->getMock();
        $container->singleton('filesystem', function () use ($filesystemFactory) {
            return $filesystemFactory;
        });
        $queueFake = new QueueFake($app);
        $mailer = $this->getMockBuilder(Mailer::class)
            ->setConstructorArgs($this->getMocks())
            ->onlyMethods(['createMessage'])
            ->getMock();
        $mailer->setQueue($queueFake);
        $mailable = new MailableQueableStub;
        $attachmentOption = ['mime' => 'image/jpeg', 'as' => 'bar.jpg'];

        $mailable->attachFromStorage('/', 'foo.jpg', $attachmentOption);

        $this->assertIsArray($mailable->diskAttachments);
        $this->assertCount(1, $mailable->diskAttachments);
        $this->assertEquals($mailable->diskAttachments[0]['options'], $attachmentOption);

        $queueFake->assertNothingPushed();
        $mailer->send($mailable);
        $queueFake->assertPushedOn(null, SendQueuedMailable::class);
    }

    protected function getMocks()
    {
        return ['smtp', m::mock(Factory::class), m::mock(Swift_Mailer::class)];
    }
}

class MailableQueableStub extends Mailable implements ShouldQueue
{
    use Queueable;

    public function build(): self
    {
        $this
            ->subject('lorem ipsum')
            ->html('foo bar baz')
            ->to('foo@example.tld');

        return $this;
    }
}
