<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Exception;
use ZipArchive;

class SmkiPenyediaBarangJasaDocxService
{
    private const W = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    /**
     * Path template dokumen FR-020.
     */
    public static function getTemplatePath(): string
    {
        $possiblePaths = [
            resource_path('templates/FR-020 Formulir Daftar Penyedia.docx'),
            base_path('resources/templates/FR-020 Formulir Daftar Penyedia.docx'),
            storage_path('app/templates/FR-020 Formulir Daftar Penyedia.docx'),
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new Exception('Template dokumen FR-020 Formulir Daftar Penyedia.docx tidak ditemukan di server.');
    }

    /**
     * Generate file DOCX terisi berdasarkan data penyedia barang/jasa.
     *
     * @param array|\Illuminate\Support\Collection $items
     * @param array $options ['no_dokumen', 'no_revisi', 'tanggal_berlaku', 'periode']
     * @return string Path file temporary docx
     */
    public function generateDocx($items, array $options = []): string
    {
        $templatePath = self::getTemplatePath();

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $outputPath = $tempDir . '/FR-020_generated_' . uniqid() . '.docx';
        if (!copy($templatePath, $outputPath)) {
            throw new Exception('Gagal menyalin file template FR-020.');
        }

        $zip = new ZipArchive();
        if ($zip->open($outputPath) !== true) {
            throw new Exception('Gagal membuka file DOCX.');
        }

        $xml = $zip->getFromName('word/document.xml');
        if (!$xml) {
            $zip->close();
            throw new Exception('Format dokumen tidak memiliki word/document.xml.');
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;
        @$dom->loadXML($xml);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', self::W);

        $tables = $xpath->query('//w:tbl');
        if ($tables->length < 2) {
            $zip->close();
            throw new Exception('Struktur tabel dalam template FR-020 tidak valid.');
        }

        // -------------------------------------------------------------
        // TABLE 0: Kop dokumen (No. Dokumen, No. Revisi, Tanggal Berlaku)
        // Kolom nilai berada di cell index ke-4 pada tiap baris.
        // -------------------------------------------------------------
        $headerTable = $tables->item(0);
        $headerRows  = $xpath->query('w:tr', $headerTable);

        if (!empty($options['no_dokumen']) && $headerRows->length > 0) {
            $this->setCellText($xpath, $dom, $headerRows->item(0), 4, $options['no_dokumen']);
        }
        if (!empty($options['no_revisi']) && $headerRows->length > 1) {
            $this->setCellText($xpath, $dom, $headerRows->item(1), 4, $options['no_revisi']);
        }
        if (!empty($options['tanggal_berlaku']) && $headerRows->length > 2) {
            $this->setCellText($xpath, $dom, $headerRows->item(2), 4, $options['tanggal_berlaku']);
        }

        // -------------------------------------------------------------
        // Periode : Tahun XXXX (teks "2022" pada template adalah nilai tetap,
        // jadi harus diganti dengan tahun periode yang dipilih/tahun berjalan).
        // -------------------------------------------------------------
        $periode = !empty($options['periode']) ? (string) $options['periode'] : date('Y');
        $yearNodes = $xpath->query("//w:p[contains(., 'Periode')]//w:t[normalize-space(.)='2022']");
        foreach ($yearNodes as $yearNode) {
            $yearNode->nodeValue = ' ' . htmlspecialchars($periode, ENT_NOQUOTES, 'UTF-8');
            $yearNode->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
        }

        // -------------------------------------------------------------
        // TABLE 1: Tabel Data Penyedia
        // Row 3 = header kolom (No, Nama Perusahaan, Alamat, No. Kontrak,
        //         Ruang Lingkup, Contact Person, No Telepon, Berita Acara)
        // Row 4 = baris kosong acuan (template row) untuk di-clone per data
        // -------------------------------------------------------------
        $dataTable = $tables->item(1);
        $rows = $xpath->query('w:tr', $dataTable);
        if ($rows->length < 5) {
            $zip->close();
            throw new Exception('Struktur baris tabel FR-020 tidak memiliki baris data acuan.');
        }

        $sampleRow = $rows->item(4);
        $templateRow = $sampleRow->cloneNode(true);

        for ($i = $rows->length - 1; $i >= 4; $i--) {
            $dataTable->removeChild($rows->item($i));
        }

        $index = 1;
        foreach ($items as $item) {
            $ruangLingkup = $this->getNestedField($item, 'ruangLingkup', 'nama_ruang_lingkup', 'ruangLingkup');

            $cellData = [
                (string) $index++,
                (string) ($this->getField($item, 'nama_perusahaan') ?: '-'),
                (string) ($this->getField($item, 'alamat') ?: '-'),
                (string) ($this->getField($item, 'no_kontrak') ?: '-'),
                (string) ($ruangLingkup ?: '-'),
                (string) ($this->getField($item, 'contact_person') ?: '-'),
                (string) ($this->getField($item, 'no_telp') ?: '-'),
                (string) ($this->getField($item, 'berita_acara') ?: '-'),
            ];

            $newRow = $templateRow->cloneNode(true);
            $cells  = $xpath->query('w:tc', $newRow);
            for ($cIdx = 0; $cIdx < count($cellData) && $cIdx < $cells->length; $cIdx++) {
                $this->writeCellText($dom, $xpath, $cells->item($cIdx), $cellData[$cIdx]);
            }
            $dataTable->appendChild($newRow);
        }

        if (count($items) === 0) {
            $newRow = $templateRow->cloneNode(true);
            $cells  = $xpath->query('w:tc', $newRow);
            $placeholder = ['1', '-', '-', '-', '-', '-', '-', '-'];
            for ($cIdx = 0; $cIdx < count($placeholder) && $cIdx < $cells->length; $cIdx++) {
                $this->writeCellText($dom, $xpath, $cells->item($cIdx), $placeholder[$cIdx]);
            }
            $dataTable->appendChild($newRow);
        }

        $zip->addFromString('word/document.xml', $dom->saveXML());
        $zip->close();

        return $outputPath;
    }

    private function getField($item, string $key)
    {
        if (is_array($item)) {
            return $item[$key] ?? null;
        }
        return $item->{$key} ?? null;
    }

    private function getNestedField($item, string $objKey, string $propKey, string $relationName)
    {
        if (is_array($item)) {
            if (isset($item[$objKey]) && is_array($item[$objKey])) {
                return $item[$objKey][$propKey] ?? null;
            }
            return null;
        }
        if (isset($item->{$relationName}) && is_object($item->{$relationName})) {
            return $item->{$relationName}->{$propKey} ?? null;
        }
        return null;
    }

    private function setCellText(DOMXPath $xpath, DOMDocument $dom, $rowNode, int $cellIndex, string $text): void
    {
        $cells = $xpath->query('w:tc', $rowNode);
        if ($cells->length > $cellIndex) {
            $this->writeCellText($dom, $xpath, $cells->item($cellIndex), $text);
        }
    }

    private function writeCellText(DOMDocument $dom, DOMXPath $xpath, $cellNode, string $text): void
    {
        $paragraphs = $xpath->query('w:p', $cellNode);
        if ($paragraphs->length === 0) {
            return;
        }
        $p = $paragraphs->item(0);

        $rPr = null;
        $existingRuns = $xpath->query('w:r', $p);
        if ($existingRuns->length > 0) {
            $existingRPr = $xpath->query('w:rPr', $existingRuns->item(0))->item(0);
            if ($existingRPr) {
                $rPr = $existingRPr->cloneNode(true);
            }
        }
        // Baris acuan template tidak punya run, jadi ambil format (Arial 9pt)
        // dari properti paragraf agar teks tidak jatuh ke font default (Times 12pt).
        if (!$rPr) {
            $paraRPr = $xpath->query('w:pPr/w:rPr', $p)->item(0);
            if ($paraRPr) {
                $rPr = $paraRPr->cloneNode(true);
            }
        }
        foreach ($existingRuns as $run) {
            $p->removeChild($run);
        }

        $newRun = $dom->createElementNS(self::W, 'w:r');
        if ($rPr) {
            $newRun->appendChild($rPr);
        }

        $newText = $dom->createElementNS(self::W, 'w:t');
        $newText->nodeValue = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
        if (trim($text) !== $text) {
            $newText->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
        }
        $newRun->appendChild($newText);
        $p->appendChild($newRun);
    }
}
