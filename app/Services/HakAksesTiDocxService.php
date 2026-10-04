<?php

namespace App\Services;

use Exception;
use ZipArchive;

/**
 * Menghasilkan dokumen Word (.docx) FR-018 Formulir Hak Akses TI.
 *
 * Dibuat langsung dari OOXML (tanpa ketergantungan template) agar selalu
 * menghasilkan dokumen yang valid walaupun struktur formulir sangat dinamis.
 */
class HakAksesTiDocxService
{
    /**
     * @param \Illuminate\Support\Collection|array $items
     * @param array $options no_dokumen, no_revisi, tanggal_berlaku
     * @return string path file sementara
     */
    public function generateDocx($items, array $options = []): string
    {
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $outputPath = $tempDir . '/FR-018_generated_' . uniqid() . '.docx';

        $zip = new ZipArchive();
        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Gagal membuat file DOCX.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('word/_rels/document.xml.rels', $this->documentRels());
        $zip->addFromString('word/styles.xml', $this->styles());
        $zip->addFromString('word/document.xml', $this->document($items, $options));

        $zip->close();

        return $outputPath;
    }

    private function document($items, array $options): string
    {
        $noDokumen = $options['no_dokumen'] ?? 'FR-018/KOM.03.05/ SANDIKAMI';
        $noRevisi = $options['no_revisi'] ?? '1.1';
        $tanggalBerlaku = $options['tanggal_berlaku'] ?? '07 Juli 2022';

        $body = '';

        $body .= $this->p('FORMULIR HAK AKSES TI', ['bold' => true, 'size' => 16, 'align' => 'center']);
        $body .= $this->p('Dinas Komunikasi dan Informatika Provinsi Jawa Barat', ['size' => 10, 'align' => 'center', 'color' => '555555']);
        $body .= $this->p(
            "No. Dokumen: {$noDokumen}    |    No. Revisi: {$noRevisi}    |    Tanggal Berlaku: {$tanggalBerlaku}",
            ['size' => 9, 'align' => 'center']
        );
        $body .= $this->p('', []);

        $items = is_array($items) ? $items : $items->all();

        if (empty($items)) {
            $body .= $this->p('Tidak ada data permohonan untuk diekspor.', ['italic' => true]);
            return $this->wrapDocument($body);
        }

        foreach ($items as $item) {
            $body .= $this->renderItem($item);
            $body .= $this->p('', []);
        }

        return $this->wrapDocument($body);
    }

    private function renderItem($item): string
    {
        $get = function ($key, $default = '-') use ($item) {
            $val = is_array($item) ? ($item[$key] ?? null) : ($item->$key ?? null);
            return ($val === null || $val === '') ? $default : $val;
        };
        $rel = function ($relation, $field, $default = '-') use ($item) {
            $obj = is_array($item) ? null : ($item->$relation ?? null);
            if (is_array($obj)) {
                return $obj[$field] ?? $default;
            }
            return $obj?->$field ?? $default;
        };

        $out = '';
        $out .= $this->p('PERMOHONAN HAK AKSES — ' . $get('nomor_request'), [
            'bold' => true, 'size' => 13, 'color' => '047857',
        ]);
        $out .= $this->p('Status: ' . $get('status_permohonan', 'Menunggu') . '   |   Sifat Akses: ' . $get('sifat_akses'), [
            'size' => 9, 'italic' => true,
        ]);

        // Data Pemohon
        $out .= $this->section('A. DATA PEMOHON');
        $rows = [
            ['Nama Lengkap', $get('nama_pemohon'), 'NIP / ID Pegawai', $get('nip_id_pegawai')],
            ['Jabatan', $get('jabatan'), 'Unit Kerja / Vendor', $rel('unitKerja', 'nama_unit')],
            ['Email', $get('email'), 'No. HP / Kontak', $get('kontak_person')],
        ];
        $out .= $this->table($rows);

        // Detail Permohonan
        $out .= $this->section('B. DETAIL PERMOHONAN AKSES');
        $rows2 = [
            ['Jenis Permohonan', $rel('jenisPermohonan', 'nama_jenis'), 'Sistem / Aplikasi', $rel('sistemAplikasi', 'nama_sistem')],
            ['Level / Area Hak Akses', $rel('levelAkses', 'nama_level'), 'Waktu Akses', $get('waktu_akses')],
            ['Masa Berlaku Mulai', $this->formatDate($get('masa_berlaku_mulai')), 'Masa Berlaku Selesai', $this->formatDate($get('masa_berlaku_selesai'))],
        ];
        $out .= $this->table($rows2);

        $jenis = [];
        if (!is_array($item) && $item->relationLoaded('jenisAkses')) {
            $jenis = $item->jenisAkses->pluck('nama_akses')->all();
        }
        $out .= $this->p('Jenis Akses: ' . (empty($jenis) ? '-' : implode(', ', $jenis)), ['size' => 10]);

        $out .= $this->p('Keperluan / Alasan Permohonan:', ['bold' => true, 'size' => 10]);
        $out .= $this->p($get('keperluan', '-'), ['size' => 10]);

        // Ketentuan
        $out .= $this->section('C. KETENTUAN PENGGUNAAN HAK AKSES');
        $ketentuan = [
            '1. User harus menyetujui dan mematuhi kebijakan keamanan informasi, kebijakan pengamanan Sistem/Aplikasi dan prosedur terkait.',
            '2. User dilarang mengalihkan dan/atau meminjamkan hak akses kepada pihak lain.',
            '3. User dilarang menyalahgunakan hak akses untuk kepentingan selain penugasan yang telah ditetapkan.',
            '4. Pelanggaran terhadap kebijakan akan menyebabkan pencabutan akses, tindakan disiplin, dan sanksi sesuai peraturan yang berlaku.',
        ];
        foreach ($ketentuan as $k) {
            $out .= $this->p($k, ['size' => 9]);
        }

        $setuju = is_array($item) ? ($item['persetujuan_ketentuan'] ?? false) : ($item->persetujuan_ketentuan ?? false);
        $out .= $this->p('Persetujuan Pemohon: ' . ($setuju ? '[X] Disetujui' : '[ ] Belum Disetujui'), [
            'bold' => true, 'size' => 10,
        ]);

        // Tanda tangan
        $out .= $this->p('', []);
        $out .= $this->signatureTable();

        return $out;
    }

    private function section(string $title): string
    {
        return $this->p($title, ['bold' => true, 'size' => 11, 'color' => '065f46']) . $this->p('', []);
    }

    private function formatDate($value): string
    {
        if (empty($value) || $value === '-') {
            return '-';
        }
        try {
            return \Carbon\Carbon::parse($value)->format('d F Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    // ============================================================
    // OOXML BUILDERS
    // ============================================================

    private function wrapDocument(string $body): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">' .
            '<w:body>' . $body .
            '<w:sectPr><w:pgSz w:w="11906" w:h="16838"/>' .
            '<w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1701" w:header="720" w:footer="720" w:gutter="0"/>' .
            '</w:sectPr>' .
            '</w:body></w:document>';
    }

    private function p(string $text, array $opts = []): string
    {
        $align = $opts['align'] ?? null;
        $pPr = '<w:pPr>';
        if ($align) {
            $pPr .= '<w:jc w:val="' . $align . '"/>';
        }
        $pPr .= '<w:spacing w:after="60"/>';
        $pPr .= '</w:pPr>';

        if ($text === '') {
            return '<w:p>' . $pPr . '</w:p>';
        }

        $rPr = $this->runProps($opts);
        $escaped = $this->esc($text);

        return '<w:p>' . $pPr . '<w:r>' . $rPr .
            '<w:t xml:space="preserve">' . $escaped . '</w:t></w:r></w:p>';
    }

    private function runProps(array $opts): string
    {
        $rPr = '';
        if (!empty($opts['bold'])) {
            $rPr .= '<w:b/><w:bCs/>';
        }
        if (!empty($opts['italic'])) {
            $rPr .= '<w:i/>';
        }
        $size = $opts['size'] ?? null;
        if ($size) {
            $half = (int) $size * 2;
            $rPr .= '<w:sz w:val="' . $half . '"/><w:szCs w:val="' . $half . '"/>';
        }
        $color = $opts['color'] ?? null;
        if ($color) {
            $rPr .= '<w:color w:val="' . $color . '"/>';
        }
        if ($rPr === '') {
            return '';
        }
        return '<w:rPr>' . $rPr . '</w:rPr>';
    }

    private function table(array $rows): string
    {
        $xml = '<w:tbl><w:tblPr><w:tblW w:w="9000" w:type="dxa"/>' .
            '<w:tblBorders>' .
            '<w:top w:val="single" w:sz="4" w:space="0" w:color="999999"/>' .
            '<w:left w:val="single" w:sz="4" w:space="0" w:color="999999"/>' .
            '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="999999"/>' .
            '<w:right w:val="single" w:sz="4" w:space="0" w:color="999999"/>' .
            '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="999999"/>' .
            '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="999999"/>' .
            '</w:tblBorders></w:tblPr>';

        foreach ($rows as $row) {
            $xml .= '<w:tr>';
            foreach ($row as $i => $cell) {
                $isLabel = ($i % 2 === 0);
                $xml .= '<w:tc><w:tcPr><w:tcW w:w="' . ($isLabel ? 3000 : 6000) . '" w:type="dxa"/>' .
                    ($isLabel ? '<w:shd w:val="clear" w:color="auto" w:fill="f1f5f9"/>' : '') .
                    '</w:tcPr>' .
                    '<w:p><w:r><w:rPr>' . ($isLabel ? '<w:b/>' : '') . '<w:sz w:val="20"/></w:rPr>' .
                    '<w:t xml:space="preserve">' . $this->esc((string) $cell) . '</w:t></w:r></w:p></w:tc>';
            }
            $xml .= '</w:tr>';
        }

        $xml .= '</w:tbl>';
        return $xml . $this->p('', []);
    }

    private function signatureTable(): string
    {
        $roles = [
            'Dibuat Oleh:' . "\n" . 'Pemohon,',
            'Diketahui Oleh:' . "\n" . 'Atasan Pemohon,',
            'Disetujui Oleh:' . "\n" . 'Penanggung Jawab Perangkat,',
            'Dilaksanakan Oleh:' . "\n" . 'Agen,',
        ];

        $xml = '<w:tbl><w:tblPr><w:tblW w:w="9000" w:type="dxa"/></w:tblPr><w:tr>';
        foreach ($roles as $role) {
            $xml .= '<w:tc><w:tcPr><w:tcW w:w="2250" w:type="dxa"/></w:tcPr>' .
                '<w:p><w:r><w:rPr><w:sz w:val="18"/></w:rPr><w:t xml:space="preserve">' . $this->esc($role) . '</w:t></w:r></w:p>' .
                '<w:p><w:r><w:rPr><w:sz w:val="18"/></w:rPr><w:t xml:space="preserve"> </w:t></w:r></w:p>' .
                '<w:p><w:r><w:rPr><w:sz w:val="18"/></w:rPr><w:t xml:space="preserve"> </w:t></w:r></w:p>' .
                '<w:p><w:r><w:rPr><w:sz w:val="18"/></w:rPr><w:t xml:space="preserve"> </w:t></w:r></w:p>' .
                '<w:p><w:r><w:rPr><w:sz w:val="18"/></w:rPr><w:t xml:space="preserve">(............................)</w:t></w:r></w:p>' .
                '</w:tc>';
        }
        $xml .= '</w:tr></w:tbl>';
        return $xml;
    }

    private function esc(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    // ============================================================
    // PACKAGE PARTS
    // ============================================================

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>' .
            '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>' .
            '</Types>';
    }

    private function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>' .
            '</Relationships>';
    }

    private function documentRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
            '</Relationships>';
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">' .
            '<w:docDefaults><w:rPrDefault><w:rPr>' .
            '<w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial"/>' .
            '<w:sz w:val="22"/><w:szCs w:val="22"/>' .
            '</w:rPr></w:rPrDefault></w:docDefaults>' .
            '<w:style w:type="paragraph" w:default="1" w:styleId="Normal">' .
            '<w:name w:val="Normal"/><w:qFormat/></w:style>' .
            '</w:styles>';
    }
}
