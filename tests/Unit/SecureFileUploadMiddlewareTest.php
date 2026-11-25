<?php

namespace Tests\Unit;

use App\Http\Middleware\SecureFileUploadMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * SECURITY (audit run-3, item 6): the content scan read only the first 1 KB
 * of each upload, so a file with a clean header and a script payload past that
 * window passed as an image. Every file is still checked (count is capped by
 * the controller), but the scan now covers the whole file.
 */
class SecureFileUploadMiddlewareTest extends TestCase
{
    private const PNG_HEADER = "\x89PNG\r\n\x1a\n";

    #[Test]
    public function a_script_payload_after_the_first_kilobyte_is_rejected(): void
    {
        $content = self::PNG_HEADER.str_repeat("\0", 8192).'<?php system($_GET["c"]); ?>';

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('File contains potentially dangerous content.');

        try {
            $this->runMiddleware(UploadedFile::fake()->createWithContent('evidence.png', $content));
        } catch (HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());

            throw $e;
        }
    }

    #[Test]
    public function a_payload_split_across_scan_chunks_is_rejected(): void
    {
        // The scanner reads in windows; a signature that straddles a window
        // boundary must still be found. 65536 is the chunk size used by the
        // scanner, so the opening tag starts just before the boundary.
        $content = self::PNG_HEADER
            .str_repeat("\0", 65536 - strlen(self::PNG_HEADER) - 2)
            .'<?php echo 1;';

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('File contains potentially dangerous content.');

        $this->runMiddleware(UploadedFile::fake()->createWithContent('evidence.png', $content));
    }

    #[Test]
    public function a_large_clean_image_passes_the_content_scan(): void
    {
        $content = self::PNG_HEADER.str_repeat("\0", 300000);

        $response = $this->runMiddleware(UploadedFile::fake()->createWithContent('evidence.png', $content));

        $this->assertSame(204, $response->getStatusCode());
    }

    #[Test]
    public function a_binary_file_that_happens_to_contain_a_short_server_tag_still_passes(): void
    {
        // Office documents are ZIP containers: high-entropy bytes in which a
        // two-byte sequence like "<%" or a three-byte "<?=" occurs by chance
        // many times over a few megabytes. Those short tags only mean
        // something in server-parsed text, so they must not reject binaries.
        $content = self::PNG_HEADER.$this->highEntropyBytesWithoutLongSignatures(4 * 1024 * 1024);
        $content .= '<%'.'?= not a real tag';

        $this->assertNotSame(false, stripos($content, '<'.'%'), 'fixture must contain the short tag');

        $response = $this->runMiddleware(UploadedFile::fake()->createWithContent('evidence.png', $content));

        $this->assertSame(204, $response->getStatusCode());
    }

    #[Test]
    public function a_text_file_containing_a_short_server_tag_is_rejected(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('File contains potentially dangerous content.');

        $this->runMiddleware(UploadedFile::fake()->createWithContent('notes.txt', "hello\n<"."% response.write(1) %>\n"));
    }

    #[Test]
    #[DataProvider('dangerousExtensions')]
    public function a_dangerous_extension_is_rejected_regardless_of_content(string $extension): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('File type not allowed for security reasons.');

        $this->runMiddleware(UploadedFile::fake()->createWithContent('report.'.$extension, self::PNG_HEADER));
    }

    /** @return array<string, array{string}> */
    public static function dangerousExtensions(): array
    {
        // Entries of SecureFileUploadMiddleware::$dangerousExtensions.
        return [['php'], ['phtml'], ['php5'], ['js'], ['sh'], ['sql']];
    }

    /**
     * Pseudo-random bytes that contain none of the long executable
     * signatures, so the fixture exercises only the short-tag decision.
     */
    private function highEntropyBytesWithoutLongSignatures(int $length): string
    {
        $long = ['<'.'?'.'php', '<'.'script', 'ev'.'al(', 'ex'.'ec(', 'sy'.'stem(', 'pa'.'ssthru(', 'sh'.'ell_exec(', 'ba'.'se64_decode('];

        $bytes = random_bytes($length);

        foreach ($long as $signature) {
            // Astronomically unlikely with random bytes; fail loudly rather
            // than skip, so the floor guard stays clean.
            $this->assertFalse(stripos($bytes, $signature) !== false, 'fixture collided with '.$signature);
        }

        return $bytes;
    }

    private function runMiddleware(UploadedFile $file): Response
    {
        $request = Request::create('/upload', 'POST', [], [], ['file' => $file]);

        return (new SecureFileUploadMiddleware)->handle($request, fn () => new Response(null, 204));
    }
}
