<?php

declare(strict_types=1);

namespace MajorsilencePdf;

/**
 * PHP FFI wrapper for the pdfnative shared library.
 *
 * Loads the Majorsilence PDF engine in-process via PHP's FFI extension
 * (php.ini: extension=ffi, ffi.enable=true) — no subprocess is spawned,
 * no .NET runtime is required on the host.
 *
 * Platform-specific library filenames:
 *   Linux:   libpdfnative.so
 *   macOS:   libpdfnative.dylib
 *   Windows: pdfnative.dll
 *
 * Usage:
 *   require_once __DIR__ . '/../src/PdfNative.php';
 *   use MajorsilencePdf\PdfLibrary;
 *   use MajorsilencePdf\PdfDocument;
 *
 *   $lib = PdfLibrary::load('/path/to/libpdfnative.so');
 *
 *   $doc = new PdfDocument($lib);
 *   $doc->setTitle('My Report');
 *   $canvas = $doc->addPage(595.28, 841.89);
 *   $canvas->drawText('Hello, PDF!', 72, 100);
 *   $canvas->close();
 *   $doc->save('/tmp/output.pdf');
 *   $doc->close();
 *
 * Page sizes (points):
 *   A4 = 595.28 × 841.89    Letter = 612 × 792
 *   A3 = 841.89 × 1190.55   Legal  = 612 × 1008
 *   A5 = 419.53 × 595.28    Tabloid= 792 × 1224
 */

/** Loads and initializes the pdfnative shared library. */
class PdfLibrary
{
    private const C_DECLS = <<<C
        int         pdf_init(void);

        void*       pdf_doc_create(void);
        int         pdf_doc_set_title(void* handle, const char* title);
        int         pdf_doc_set_author(void* handle, const char* author);
        int         pdf_doc_set_subject(void* handle, const char* subject);
        int         pdf_doc_set_creator(void* handle, const char* creator);
        int         pdf_doc_set_security(void* handle, const char* user_password,
                        const char* owner_password, int permissions, int enc_version);
        void*       pdf_doc_add_page(void* handle, float width, float height);
        int         pdf_doc_save_file(void* handle, const char* path);
        void        pdf_doc_close(void* handle);

        int         pdf_canvas_draw_text(void* canvas, const char* text,
                        float x, float y, void* style);
        int         pdf_canvas_draw_textbox(void* canvas, const char* text,
                        float x, float y, float width, float height,
                        void* style, float line_spacing);
        int         pdf_canvas_draw_line(void* canvas,
                        float x1, float y1, float x2, float y2,
                        uint8_t r, uint8_t g, uint8_t b, float width);
        int         pdf_canvas_draw_rect(void* canvas,
                        float x, float y, float width, float height,
                        uint8_t fill_r, uint8_t fill_g, uint8_t fill_b, int has_fill,
                        uint8_t stroke_r, uint8_t stroke_g, uint8_t stroke_b,
                        float stroke_width, int has_stroke);
        int         pdf_canvas_draw_ellipse(void* canvas,
                        float x, float y, float width, float height,
                        uint8_t fill_r, uint8_t fill_g, uint8_t fill_b, int has_fill,
                        uint8_t stroke_r, uint8_t stroke_g, uint8_t stroke_b,
                        float stroke_width, int has_stroke);
        int         pdf_canvas_draw_table(void* canvas, void* table, float x, float y);
        int         pdf_canvas_add_link(void* canvas, float x, float y,
                        float width, float height, const char* uri);
        void        pdf_canvas_close(void* handle);

        void*       pdf_style_create(void);
        int         pdf_style_set_font_family(void* style, const char* family);
        int         pdf_style_set_font_file(void* style, const char* path);
        int         pdf_style_set_size(void* style, float points);
        int         pdf_style_set_color(void* style, uint8_t r, uint8_t g, uint8_t b);
        int         pdf_style_set_bold(void* style, int is_bold);
        int         pdf_style_set_italic(void* style, int is_italic);
        int         pdf_style_set_alignment(void* style, int alignment);
        int         pdf_style_set_decoration(void* style, int decoration);
        void        pdf_style_close(void* handle);

        void*       pdf_table_create(float* col_widths, int n_cols);
        int         pdf_table_set_header_bg(void* handle, uint8_t r, uint8_t g, uint8_t b);
        int         pdf_table_set_alternate_bg(void* handle, uint8_t r, uint8_t g, uint8_t b);
        int         pdf_table_set_border(void* handle, uint8_t r, uint8_t g, uint8_t b, float width);
        int         pdf_table_set_cell_padding(void* handle, float padding);
        int         pdf_table_stage_cell(void* handle, const char* text);
        int         pdf_table_commit_row(void* handle);
        void        pdf_table_close(void* handle);

        void*       pdf_merge_create(void);
        int         pdf_merge_add_bytes(void* handle, uint8_t* data, int data_len);
        int         pdf_merge_save_file(void* handle, const char* path);
        void        pdf_merge_close(void* handle);

        void        pdf_free(void* ptr);
        const char* pdf_last_error(void);
    C;

    /**
     * Load the shared library from $lib_path and initialize the engine.
     * Returns an \FFI instance to pass to PdfDocument, PdfStyle, etc.
     * @throws \RuntimeException on init failure
     */
    public static function load(string $lib_path): \FFI
    {
        putenv('PDFNATIVE_LIB_DIR=' . dirname((string) realpath($lib_path)));
        $ffi = \FFI::cdef(self::C_DECLS, $lib_path);
        $ret = $ffi->pdf_init();
        if ($ret !== 0) {
            throw new \RuntimeException('pdf_init failed: ' . \FFI::string($ffi->pdf_last_error()));
        }
        return $ffi;
    }
}

// ── Style alignment constants ──────────────────────────────────────────────────
const ALIGN_LEFT   = 0;
const ALIGN_CENTER = 1;
const ALIGN_RIGHT  = 2;

// ── Text decoration constants ──────────────────────────────────────────────────
const DECOR_NONE          = 0;
const DECOR_UNDERLINE     = 1;
const DECOR_STRIKETHROUGH = 2;
const DECOR_OVERLINE      = 3;

// ── Encryption permission flags ───────────────────────────────────────────────
const PERM_PRINT              =    4;
const PERM_MODIFY_CONTENT     =    8;
const PERM_COPY_TEXT          =   16;
const PERM_ADD_ANNOTATIONS    =   32;
const PERM_FILL_FORMS         =  256;
const PERM_EXTRACT_TEXT       =  512;
const PERM_ASSEMBLE           = 1024;
const PERM_PRINT_HIGH_QUALITY = 2048;
const PERM_ALL                =   -1;

/** A PDF document. Call close() or use a try/finally block. */
class PdfDocument
{
    private mixed $handle;

    public function __construct(private readonly \FFI $ffi)
    {
        $h = $ffi->pdf_doc_create();
        if ($h === null) {
            $this->throwLastError('pdf_doc_create');
        }
        $this->handle = $h;
    }

    public function setTitle(string $title): static
    {
        $this->checkResult($this->ffi->pdf_doc_set_title($this->handle, $title), 'pdf_doc_set_title');
        return $this;
    }

    public function setAuthor(string $author): static
    {
        $this->checkResult($this->ffi->pdf_doc_set_author($this->handle, $author), 'pdf_doc_set_author');
        return $this;
    }

    public function setSubject(string $subject): static
    {
        $this->checkResult($this->ffi->pdf_doc_set_subject($this->handle, $subject), 'pdf_doc_set_subject');
        return $this;
    }

    public function setCreator(string $creator): static
    {
        $this->checkResult($this->ffi->pdf_doc_set_creator($this->handle, $creator), 'pdf_doc_set_creator');
        return $this;
    }

    /**
     * Apply password-based AES encryption.
     *
     * @param string      $userPassword  Password to open the document.
     * @param string|null $ownerPassword Full-control password (null = same as userPassword).
     * @param int         $permissions   Bitmask of PERM_* constants; -1 = allow all.
     * @param bool        $aes256        true = AES-256 (default), false = AES-128.
     */
    public function setSecurity(
        string  $userPassword  = '',
        ?string $ownerPassword = null,
        int     $permissions   = PERM_ALL,
        bool    $aes256        = true,
    ): static {
        $this->checkResult(
            $this->ffi->pdf_doc_set_security(
                $this->handle,
                $userPassword,
                $ownerPassword,
                $permissions,
                $aes256 ? 0 : 1,
            ),
            'pdf_doc_set_security',
        );
        return $this;
    }

    /**
     * Add a page and return a PdfCanvas for drawing.
     * Standard A4 = 595.28 × 841.89 pts.
     */
    public function addPage(float $width = 595.28, float $height = 841.89): PdfCanvas
    {
        $h = $this->ffi->pdf_doc_add_page($this->handle, $width, $height);
        if ($h === null) {
            $this->throwLastError('pdf_doc_add_page');
        }
        return new PdfCanvas($this->ffi, $h);
    }

    /** Write the completed document to $path. */
    public function save(string $path): void
    {
        $this->checkResult($this->ffi->pdf_doc_save_file($this->handle, $path), 'pdf_doc_save_file');
    }

    /** Write the completed document to memory and return PDF bytes. */
    public function saveToMemory(): string
    {
        $tmpPath = (string) tempnam(sys_get_temp_dir(), 'pdfnative');
        try {
            $this->save($tmpPath);
            return (string) file_get_contents($tmpPath);
        } finally {
            if (file_exists($tmpPath)) {
                unlink($tmpPath);
            }
        }
    }

    public function close(): void
    {
        if ($this->handle !== null) {
            $this->ffi->pdf_doc_close($this->handle);
            $this->handle = null;
        }
    }

    private function checkResult(int $ret, string $fn): void
    {
        if ($ret !== 0) {
            $this->throwLastError($fn);
        }
    }

    /** @throws \RuntimeException */
    private function throwLastError(string $fn): never
    {
        throw new \RuntimeException("{$fn} failed: " . \FFI::string($this->ffi->pdf_last_error()));
    }
}

/** A drawing canvas for one page. Call close() when done drawing. */
class PdfCanvas
{
    private mixed $handle;

    public function __construct(private readonly \FFI $ffi, mixed $handle)
    {
        $this->handle = $handle;
    }

    /**
     * Draw $text with its baseline at ($x, $y).
     * @param PdfStyle|null $style null = default style (Helvetica 12 pt black).
     */
    public function drawText(string $text, float $x, float $y, ?PdfStyle $style = null): static
    {
        $this->checkResult(
            $this->ffi->pdf_canvas_draw_text($this->handle, $text, $x, $y, $style?->handle()),
            'pdf_canvas_draw_text',
        );
        return $this;
    }

    /**
     * Draw word-wrapped text in a box.
     * Returns the index of the first character that did not fit.
     */
    public function drawTextbox(
        string   $text,
        float    $x,
        float    $y,
        float    $width,
        float    $height,
        ?PdfStyle $style       = null,
        float    $lineSpacing  = 0.0,
    ): int {
        $ret = $this->ffi->pdf_canvas_draw_textbox(
            $this->handle, $text, $x, $y, $width, $height, $style?->handle(), $lineSpacing,
        );
        if ($ret < 0) {
            $this->throwLastError('pdf_canvas_draw_textbox');
        }
        return $ret;
    }

    /** Draw a straight line. r, g, b are 0-255. */
    public function drawLine(
        float $x1, float $y1, float $x2, float $y2,
        int $r = 0, int $g = 0, int $b = 0, float $width = 1.0,
    ): static {
        $this->checkResult(
            $this->ffi->pdf_canvas_draw_line($this->handle, $x1, $y1, $x2, $y2, $r, $g, $b, $width),
            'pdf_canvas_draw_line',
        );
        return $this;
    }

    /**
     * Draw a rectangle.
     * @param array{int,int,int}|null $fillRgb   [r, g, b] or null for no fill.
     * @param array{int,int,int}|null $strokeRgb [r, g, b] or null for no stroke.
     */
    public function drawRect(
        float  $x, float  $y, float  $width, float  $height,
        ?array $fillRgb   = null,
        ?array $strokeRgb = null,
        float  $strokeWidth = 1.0,
    ): static {
        [$fr, $fg, $fb] = $fillRgb   ?? [0, 0, 0];
        [$sr, $sg, $sb] = $strokeRgb ?? [0, 0, 0];
        $this->checkResult(
            $this->ffi->pdf_canvas_draw_rect(
                $this->handle, $x, $y, $width, $height,
                $fr, $fg, $fb, $fillRgb   !== null ? 1 : 0,
                $sr, $sg, $sb, $strokeWidth, $strokeRgb !== null ? 1 : 0,
            ),
            'pdf_canvas_draw_rect',
        );
        return $this;
    }

    /**
     * Draw an ellipse bounded by the given rectangle.
     * @param array{int,int,int}|null $fillRgb
     * @param array{int,int,int}|null $strokeRgb
     */
    public function drawEllipse(
        float  $x, float  $y, float  $width, float  $height,
        ?array $fillRgb   = null,
        ?array $strokeRgb = null,
        float  $strokeWidth = 1.0,
    ): static {
        [$fr, $fg, $fb] = $fillRgb   ?? [0, 0, 0];
        [$sr, $sg, $sb] = $strokeRgb ?? [0, 0, 0];
        $this->checkResult(
            $this->ffi->pdf_canvas_draw_ellipse(
                $this->handle, $x, $y, $width, $height,
                $fr, $fg, $fb, $fillRgb   !== null ? 1 : 0,
                $sr, $sg, $sb, $strokeWidth, $strokeRgb !== null ? 1 : 0,
            ),
            'pdf_canvas_draw_ellipse',
        );
        return $this;
    }

    /** Draw a PdfTable with its top-left corner at ($x, $y). */
    public function drawTable(PdfTable $table, float $x, float $y): static
    {
        $this->checkResult(
            $this->ffi->pdf_canvas_draw_table($this->handle, $table->handle(), $x, $y),
            'pdf_canvas_draw_table',
        );
        return $this;
    }

    /** Add a clickable hyperlink over the given rectangle. */
    public function addLink(float $x, float $y, float $width, float $height, string $uri): static
    {
        $this->checkResult(
            $this->ffi->pdf_canvas_add_link($this->handle, $x, $y, $width, $height, $uri),
            'pdf_canvas_add_link',
        );
        return $this;
    }

    public function close(): void
    {
        if ($this->handle !== null) {
            $this->ffi->pdf_canvas_close($this->handle);
            $this->handle = null;
        }
    }

    private function checkResult(int $ret, string $fn): void
    {
        if ($ret !== 0) {
            $this->throwLastError($fn);
        }
    }

    /** @throws \RuntimeException */
    private function throwLastError(string $fn): never
    {
        throw new \RuntimeException("{$fn} failed: " . \FFI::string($this->ffi->pdf_last_error()));
    }
}

/**
 * A text style handle.
 * Defaults: Helvetica, 12 pt, black, left-aligned, no decoration.
 * Call close() when done.
 */
class PdfStyle
{
    private mixed $h;

    public function __construct(private readonly \FFI $ffi)
    {
        $h = $ffi->pdf_style_create();
        if ($h === null) {
            throw new \RuntimeException('pdf_style_create failed: ' . \FFI::string($ffi->pdf_last_error()));
        }
        $this->h = $h;
    }

    public function handle(): mixed { return $this->h; }

    public function setFontFamily(string $family): static
    {
        $this->ffi->pdf_style_set_font_family($this->h, $family);
        return $this;
    }

    public function setFontFile(string $path): static
    {
        $this->ffi->pdf_style_set_font_file($this->h, $path);
        return $this;
    }

    public function setSize(float $points): static
    {
        $this->ffi->pdf_style_set_size($this->h, $points);
        return $this;
    }

    public function setColor(int $r, int $g, int $b): static
    {
        $this->ffi->pdf_style_set_color($this->h, $r, $g, $b);
        return $this;
    }

    public function setBold(bool $bold = true): static
    {
        $this->ffi->pdf_style_set_bold($this->h, $bold ? 1 : 0);
        return $this;
    }

    public function setItalic(bool $italic = true): static
    {
        $this->ffi->pdf_style_set_italic($this->h, $italic ? 1 : 0);
        return $this;
    }

    /** 0 = left, 1 = center, 2 = right.  Use ALIGN_* constants. */
    public function setAlignment(int $alignment): static
    {
        $this->ffi->pdf_style_set_alignment($this->h, $alignment);
        return $this;
    }

    /** 0 = none, 1 = underline, 2 = strikethrough, 3 = overline.  Use DECOR_* constants. */
    public function setDecoration(int $decoration): static
    {
        $this->ffi->pdf_style_set_decoration($this->h, $decoration);
        return $this;
    }

    public function close(): void
    {
        if ($this->h !== null) {
            $this->ffi->pdf_style_close($this->h);
            $this->h = null;
        }
    }
}

/**
 * A table layout handle.
 *
 * Usage:
 *   $table = new PdfTable($ffi, [180, 80, 90, 90]);
 *   $table->setHeaderBg(26, 86, 160)
 *         ->setAlternateBg(240, 245, 252)
 *         ->setBorder(200, 200, 200, 0.5)
 *         ->addRow('Product', 'Qty', 'Unit Price', 'Total')
 *         ->addRow('Widget',  '3',  '$10.00',     '$30.00');
 *   $canvas->drawTable($table, 72, 100);
 *   $table->close();
 */
class PdfTable
{
    private mixed $handle;

    /** @param float[] $colWidths Column widths in points. */
    public function __construct(private readonly \FFI $ffi, array $colWidths)
    {
        $n   = count($colWidths);
        $arr = \FFI::new("float[{$n}]");
        foreach ($colWidths as $i => $w) {
            $arr[$i] = (float) $w;
        }
        $h = $ffi->pdf_table_create($arr, $n);
        if ($h === null) {
            throw new \RuntimeException('pdf_table_create failed: ' . \FFI::string($ffi->pdf_last_error()));
        }
        $this->handle = $h;
    }

    public function handle(): mixed { return $this->handle; }

    public function setHeaderBg(int $r, int $g, int $b): static
    {
        $this->ffi->pdf_table_set_header_bg($this->handle, $r, $g, $b);
        return $this;
    }

    public function setAlternateBg(int $r, int $g, int $b): static
    {
        $this->ffi->pdf_table_set_alternate_bg($this->handle, $r, $g, $b);
        return $this;
    }

    public function setBorder(int $r, int $g, int $b, float $width): static
    {
        $this->ffi->pdf_table_set_border($this->handle, $r, $g, $b, $width);
        return $this;
    }

    public function setCellPadding(float $padding): static
    {
        $this->ffi->pdf_table_set_cell_padding($this->handle, $padding);
        return $this;
    }

    /** Stage all $cells and commit them as a single row. */
    public function addRow(string ...$cells): static
    {
        foreach ($cells as $cell) {
            $ret = $this->ffi->pdf_table_stage_cell($this->handle, $cell);
            if ($ret !== 0) {
                throw new \RuntimeException('pdf_table_stage_cell failed: ' . \FFI::string($this->ffi->pdf_last_error()));
            }
        }
        $ret = $this->ffi->pdf_table_commit_row($this->handle);
        if ($ret !== 0) {
            throw new \RuntimeException('pdf_table_commit_row failed: ' . \FFI::string($this->ffi->pdf_last_error()));
        }
        return $this;
    }

    public function close(): void
    {
        if ($this->handle !== null) {
            $this->ffi->pdf_table_close($this->handle);
            $this->handle = null;
        }
    }
}

/**
 * Merges multiple PDF documents into one.
 *
 * Usage:
 *   $merger = new PdfMerger($ffi);
 *   $merger->addBytes(file_get_contents('a.pdf'));
 *   $merger->addBytes(file_get_contents('b.pdf'));
 *   $merger->save('/tmp/merged.pdf');
 *   $merger->close();
 */
class PdfMerger
{
    private mixed $handle;

    public function __construct(private readonly \FFI $ffi)
    {
        $h = $ffi->pdf_merge_create();
        if ($h === null) {
            throw new \RuntimeException('pdf_merge_create failed: ' . \FFI::string($ffi->pdf_last_error()));
        }
        $this->handle = $h;
    }

    /** Add a PDF supplied as a binary string to the merge queue. */
    public function addBytes(string $data): static
    {
        $len = strlen($data);
        $buf = \FFI::new("uint8_t[{$len}]");
        \FFI::memcpy($buf, $data, $len);
        $ret = $this->ffi->pdf_merge_add_bytes($this->handle, $buf, $len);
        if ($ret !== 0) {
            throw new \RuntimeException('pdf_merge_add_bytes failed: ' . \FFI::string($this->ffi->pdf_last_error()));
        }
        return $this;
    }

    public function save(string $path): void
    {
        $ret = $this->ffi->pdf_merge_save_file($this->handle, $path);
        if ($ret !== 0) {
            throw new \RuntimeException('pdf_merge_save_file failed: ' . \FFI::string($this->ffi->pdf_last_error()));
        }
    }

    /** Merge and return PDF bytes. */
    public function saveToMemory(): string
    {
        $tmpPath = (string) tempnam(sys_get_temp_dir(), 'pdfnative');
        try {
            $this->save($tmpPath);
            return (string) file_get_contents($tmpPath);
        } finally {
            if (file_exists($tmpPath)) {
                unlink($tmpPath);
            }
        }
    }

    public function close(): void
    {
        if ($this->handle !== null) {
            $this->ffi->pdf_merge_close($this->handle);
            $this->handle = null;
        }
    }
}
