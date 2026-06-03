<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AcademicParserService
{
    public function parse(string $rawText): array
    {
        $text = $this->normalize($rawText);
        $fields = $this->extractFields($text, $rawText);
        $courses = $this->extractCourses($text);

        Log::info('Academic Parser Result', [
            'fields' => $fields,
            'course_count' => count($courses),
        ]);

        return compact('fields', 'courses');
    }

    private function normalize(string $text): string
    {
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text);
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[^\S\n]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = preg_replace('/[‐‑–—]/', '-', $text);
        $text = preg_replace('/[ᅟᅠ　]+/', ' ', $text);
        return trim($text);
    }

    public function extractFields(string $text, string $original): array
    {
        return [
            'student_name' => $this->findStudentName($text, $original),
            'student_number' => $this->findStudentNumber($text),
            'nik' => $this->findNik($text),
            'university' => $this->findUniversity($text),
            'faculty' => $this->findFaculty($text),
            'study_program' => $this->findStudyProgram($text),
            'gelar' => $this->findGelar($text),
            'tanggal_lulus' => $this->findTanggalLulus($text),
            'gpa' => $this->findGpa($text),
            'total_sks' => $this->findTotalSks($text),
            'predikat_kelulusan' => $this->findPredikat($text),
            'nomor_transkrip' => $this->findNomorTranskrip($text),
            'graduation_year' => $this->findGraduationYear($text),
        ];
    }

    private function findStudentName(string $text, string $original): ?string
    {
        $candidates = [];
        $lines = explode("\n", $text);

        foreach ($lines as $i => $line) {
            $trimmed = trim($line);

            if (preg_match('/menyatakan\s+bahwa/i', $trimmed, $m)) {
                if (isset($lines[$i + 1])) {
                    $nextLine = trim($lines[$i + 1]);
                    if (preg_match('/^[A-Z][A-Z\s]+$/', $nextLine)) {
                        $candidates[] = $nextLine;
                    }
                }
            }

            if (preg_match('/N\s*[aA]m\s*[\.:]\s*([A-Z][A-Z\s]+?)$/i', $trimmed, $m)) {
                $candidates[] = trim($m[1]);
            }

            if (preg_match('/Nama\s*(?:Mahasiswa)?\s*[:\s]\s*([A-Z][A-Z\s]+?)$/i', $trimmed, $m)) {
                $candidates[] = trim($m[1]);
            }

            if (preg_match('/NAMA\s*[:.]?\s*([A-Z][A-Z\s]+?)$/i', $trimmed, $m)) {
                $candidates[] = trim($m[1]);
            }

            if (preg_match('/^N\s+([A-Z].+)$/', $trimmed, $m)) {
                $cleaned = str_replace(' ', '', $m[1]);
                if (strlen($cleaned) > 15) {
                    $reconstructed = preg_replace('/([A-Z])([A-Z][a-z])/', '$1 $2', $cleaned);
                    $reconstructed = preg_replace('/([a-z])([A-Z])/', '$1 $2', $reconstructed);
                    if (!preg_match('/\d/', $reconstructed)) {
                        $candidates[] = $reconstructed;
                    }
                }
            }

            if (preg_match('/^M\s+([A-Z].+)$/', $trimmed, $m)) {
                $cleaned = str_replace(' ', '', $m[1]);
                if (strlen($cleaned) > 15) {
                    $reconstructed = preg_replace('/([A-Z])([A-Z][a-z])/', '$1 $2', $cleaned);
                    $reconstructed = preg_replace('/([a-z])([A-Z])/', '$1 $2', $reconstructed);
                    if (!preg_match('/\d/', $reconstructed)) {
                        $candidates[] = $reconstructed;
                    }
                }
            }
        }

        if (preg_match('/[A-Z]{3,}\s+[A-Z]{3,}\s+[A-Z]{3,}/', $text, $m)) {
            $candidates[] = trim($m[0]);
        }

        $seen = [];
        $valid = [];
        foreach ($candidates as $name) {
            $name = trim(preg_replace('/\s+/', ' ', $name));
            $key = str_replace(' ', '', $name);
            if (strlen($name) > 10 && !isset($seen[$key])) {
                $seen[$key] = true;
                $score = 0;
                $score += preg_match('/^[A-Z\s]+$/', $name) ? 3 : 0;
                $score += substr_count($name, ' ') >= 2 ? 2 : 0;
                $score -= preg_match('/\d/', $name) ? 5 : 0;
                $score -= preg_match('/\b[A-Z]{1,2}\s+\b/', $name) ? 3 : 0;
                $valid[] = compact('name', 'score');
            }
        }

        usort($valid, fn($a, $b) => $b['score'] <=> $a['score']);

        return !empty($valid) ? $valid[0]['name'] : null;
    }

    private function findStudentNumber(string $text): ?string
    {
        $patterns = [
            '/\((\d{8,12})\)/',
            '/StudentI\.?\s*D\s*[.:]*\s*(\d{8,12})/i',
            '/\bNIM\s*[:\s]*(\d{8,12})/i',
            '/I\.?\s*M\s*[:\s]*(\d{8,12})/i',
            '/\bI\.D\s*[.:]*\s*(\d{8,12})/i',
            '/(\d{9})\b/',
        ];

        $nimCandidates = [];
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                foreach ($matches[1] as $m) {
                    $nimCandidates[] = $m;
                }
            }
        }

        $seen = [];
        foreach ($nimCandidates as $nim) {
            $nim = trim($nim);
            if (strlen($nim) >= 8 && strlen($nim) <= 12 && is_numeric($nim) && !isset($seen[$nim])) {
                $seen[$nim] = true;
                return $nim;
            }
        }

        return null;
    }

    private function findNik(string $text): ?string
    {
        $patterns = [
            '/\(NIK\)\s*[:\s]*(\d{16})/',
            '/NIK\s*[:]\s*(\d{16})/',
            '/Nomor\s+Induk\s+Kependudukan[^:]*:\s*(\d{16})/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                return $m[1];
            }
        }

        if (preg_match('/\b(\d{16})\b/', $text, $m)) {
            return $m[1];
        }

        return null;
    }

    private function findUniversity(string $text): ?string
    {
        if (preg_match('/INSTITUT TEKNOLOGI NASIONAL/i', $text, $m)) {
            return $m[0];
        }
        if (preg_match('/INSTITUT\s+\w+/i', $text, $m)) {
            return $m[0];
        }
        if (preg_match('/UNIVERSITAS\s+\w+/i', $text, $m)) {
            return $m[0];
        }
        return null;
    }

    private function findFaculty(string $text): ?string
    {
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^Fakultas\s+(.+)$/i', $trimmed, $m)) {
                return trim($m[1]);
            }
            if (preg_match('/^FAKULTAS\s+(.+)$/i', $trimmed, $m)) {
                return trim($m[1]);
            }
            if (preg_match('/^Fa[c]ulty\s*[.:]\s*(.+)$/i', $trimmed, $m)) {
                return trim(trim($m[1]), '.');
            }
        }

        $known = ['TEKNOLOGI INDUSTRI', 'INDUSTRIAL TECHNOLOGY', 'TEKNIK', 'EKONOMI', 'HUKUM', 'KEDOKTERAN'];
        foreach ($known as $f) {
            if (stripos($text, $f) !== false) return $f;
        }

        return null;
    }

    private function findStudyProgram(string $text): ?string
    {
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^Program\s+Studi\s+(.+)$/i', $trimmed, $m)) return trim($m[1]);
            if (preg_match('/^PROGRAM\s+STUDI\s+(.+)$/i', $trimmed, $m)) return trim($m[1]);
            if (preg_match('/^Study\s*\.?\s*Program\s*[.:]\s*(.+)$/i', $trimmed, $m)) return trim($m[1]);
        }

        $known = ['INFORMATIKA', 'INFORMATICS', 'TEKNIK INFORMATIKA', 'SISTEM INFORMASI', 'TEKNIK KOMPUTER'];
        foreach ($known as $p) {
            if (stripos($text, $p) !== false) return $p;
        }

        return null;
    }

    private function findGelar(string $text): ?string
    {
        $lines = explode("\n", $text);
        foreach ($lines as $i => $line) {
            $trimmed = trim($line);
            if (preg_match('/diberikan\s+gelar/i', $trimmed)) {
                if (isset($lines[$i + 1])) {
                    $nextLine = trim($lines[$i + 1]);
                    if (!empty($nextLine)) return trim($nextLine, '.');
                }
            }
            if (preg_match('/^gelar/i', $trimmed)) {
                if (isset($lines[$i + 1])) {
                    $nextLine = trim($lines[$i + 1]);
                    if (!empty($nextLine)) return trim($nextLine, '.');
                }
            }
        }

        if (preg_match('/SARJANA\s+\w+(?:\s*\([^)]+\))?/', $text, $m)) {
            return $m[0];
        }

        return null;
    }

    private function findTanggalLulus(string $text): ?string
    {
        $patterns = [
            '/lulus\s+program\s+pendidikan\s+\w+\s+tanggal\s+(\d+\s+\w+\s+\d{4})/i',
            '/Lulus\s+Program\s+\w+\s+Tanggal\s+(\d+\s+\w+\s+\d{4})/i',
            '/Graduated\s+on\s+(\d+\w*\s+\w+\s+\d{4})/i',
            '/dinyatakan\s+lulus\b[^.]*?tanggal\s+(\d+\s+\w+\s+\d{4})/i',
        ];

        $dateStr = null;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $dateStr = trim($m[1]);
                if (strlen($dateStr) > 5) break;
            }
        }

        if ($dateStr) {
            return $this->normalizeDateString($dateStr);
        }

        return null;
    }

    private function normalizeDateString(string $dateStr): string
    {
        $months = [
            'januari' => '01', 'februari' => '02', 'maret' => '03', 'april' => '04',
            'mei' => '05', 'juni' => '06', 'juli' => '07', 'agustus' => '08',
            'september' => '09', 'oktober' => '10', 'november' => '11', 'desember' => '12',
            'january' => '01', 'february' => '02', 'march' => '03', 'april' => '04',
            'may' => '05', 'june' => '06', 'july' => '07', 'august' => '08',
            'september' => '09', 'october' => '10', 'november' => '11', 'december' => '12',
        ];

        $dateStr = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $dateStr);
        $dateStr = preg_replace('/\s+/', ' ', trim($dateStr));

        if (preg_match('/^(\d{1,2})\s+([a-zA-Z]+)\s+(\d{4})$/', $dateStr, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $month = strtolower($m[2]);
            $year = $m[3];
            if (isset($months[$month])) {
                return "{$year}-{$months[$month]}-{$day}";
            }
        }

        if (preg_match('/^(\d{1,2})([a-zA-Z]+)\s+(\d{4})$/', $dateStr, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $month = strtolower($m[2]);
            $year = $m[3];
            if (isset($months[$month])) {
                return "{$year}-{$months[$month]}-{$day}";
            }
        }

        return $dateStr;
    }

    private function findGpa(string $text): ?string
    {
        $patterns = [
            '/Indeks\s*Prestasi\s*[:.\s]*(\d+[.,]\d+)/i',
            '/IPK\s*[:.\s]*(\d+[.,]\d+)/i',
            '/GPA\s*[:.\s]*(\d+[.,]\d+)/i',
            '/\bGPA(\d+[.,]\d+)\b/i',
            '/\bIPK\s*[:]*\s*(\d+[.,]\d+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $gpa = str_replace(',', '.', trim($m[1]));
                $val = (float) $gpa;
                if ($val > 0 && $val <= 4) return $gpa;
            }
        }

        return null;
    }

    private function findTotalSks(string $text): ?string
    {
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/Jumlah\s+SKS\w*\s*[:]*\s*(\d+)/i', $trimmed, $m) ||
                preg_match('/SKS\w*\s*[:]*\s*(\d+)/i', $trimmed, $m) ||
                preg_match('/Total\s+Credit\s*[:]*\s*(\d+)/i', $trimmed, $m)) {
                $sks = (int) $m[1];
                if ($sks >= 100 && $sks <= 200) return (string) $sks;
            }
        }

        if (preg_match('/\bSKS[^a-z]*(\d{3})\b/i', $text, $m)) {
            $sks = (int) $m[1];
            if ($sks >= 100 && $sks <= 200) return (string) $sks;
        }

        return null;
    }

    private function findPredikat(string $text): ?string
    {
        $lines = explode("\n", $text);
        foreach ($lines as $i => $line) {
            $trimmed = trim($line);
            if (preg_match('/^Predikat\s+Kelulusan\s*[:.\s]*(.+)$/i', $trimmed, $m)) {
                $val = trim($m[1]);
                if (!empty($val) && !preg_match('/^[A-Z]\s/', $val)) return $val;
            }
            if (preg_match('/^PREDIKAT\s+KELULUSAN\s*[:.\s]*(.+)$/i', $trimmed, $m)) {
                $val = trim($m[1]);
                if (!empty($val)) return $val;
            }
            if (preg_match('/^Predikat\s*[:.\s]*(.+)$/i', $trimmed, $m)) {
                $val = trim($m[1]);
                if (!empty($val) && stripos($val, 'kelulusan') === false && !preg_match('/^\d/', $val)) return $val;
            }
            if (preg_match('/^PREDIKAT\s*(?:KELULUSAN)?\s*[:.\s]*(.+)$/i', $trimmed, $m)) {
                $val = trim($m[1]);
                if (!empty($val) && stripos($val, 'kelulusan') === false) return $val;
            }
            if (preg_match('/^Predicate\s*[:.\s]*(.+)$/i', $trimmed, $m)) {
                $val = trim($m[1]);
                if (!empty($val)) return $val;
            }
        }

        $known = ['Sangat Memuaskan', 'Sangat Baik', 'Memuaskan', 'Baik', 'Pujian', 'Cum Laude', 'Excellent', 'Very Good'];
        foreach ($known as $p) {
            if (stripos($text, $p) !== false) return $p;
        }

        return null;
    }

    private function findNomorTranskrip(string $text): ?string
    {
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^Transkrip\s*[:]*\s*(\S+)$/i', $trimmed, $m)) return trim($m[1]);
            if (preg_match('/^Transkip\s*[:]*\s*(\S+)$/i', $trimmed, $m)) return trim($m[1]);
            if (preg_match('/^Transcript\s*Number\s*[:]*\s*(\S+)/i', $trimmed, $m)) {
                return trim(preg_replace('/[A-Z].*$/', '', $m[1]));
            }
            if (preg_match('/^omoTranskip(\S+)$/i', $trimmed, $m)) return trim($m[1]);
        }

        $patterns = [
            '/\b(\d{4}\/[A-Z]+\/[\d.]+\/[A-Z]+\/\w+\/\d{4})\b/',
            '/\b(\d{4}\/\w+\/[\d.]+\/[A-Z]+(?:TENAS|ITENAS)\/\w+\/\d{4})\b/',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) return $m[1];
        }

        if (preg_match('/\b(\d{4}\/\w+\/\d+\.\d+\.\d+\/[A-Z]+\/\w+\/\d{4})\b/', $text, $m)) {
            return $m[1];
        }

        return null;
    }

    private function findGraduationYear(string $text): ?string
    {
        if (preg_match('/\b(20[2-9]\d)\b/', $text, $m)) {
            return $m[1];
        }
        return null;
    }

    public function extractCourses(string $text): array
    {
        $lines = explode("\n", $text);
        $startLine = null;

        foreach ($lines as $i => $line) {
            if (preg_match('/MATA\s*KULIAH|MATAKULIAI|CREDIT\s+GRADE/i', $line)) {
                $startLine = $i + 1;
                break;
            }
        }

        if ($startLine === null) return [];

        $raw = [];
        for ($j = $startLine; $j < count($lines); $j++) {
            $trimmed = trim($lines[$j]);
            if (preg_match('/^(?:Jumlah|Total|Indeks|IPK|GPA|Predikat|Predicate|Judul|Thesis|Lulus|Graduated|Dekan|Dean|Bandung|Prof\.|Jalan|itenas|NNOLOGI)/i', $trimmed)) break;
            if (preg_match('/^SKO|MILA|NILAI|SKS|GRADE|MATA|CREDIT|HURUF|MUTU|ANGKA/i', $trimmed)) continue;
            if (empty($trimmed)) {
                $raw[] = '';
                continue;
            }
            $raw[] = $trimmed;
        }

        $courseCodeRegex = '/^[A-Z0-9]{1,4}-+\d{2,4}$/';

        $courseBlocks = [];
        $currentCode = null;

        foreach ($raw as $line) {
            if (empty($line)) continue;

            if (preg_match($courseCodeRegex, $line)) {
                $currentCode = $line;
                $courseBlocks[$currentCode] = ['name' => '', 'details' => []];
                continue;
            }

            if ($currentCode !== null) {
                $courseBlocks[$currentCode]['details'][] = $line;
            }
        }

        $gradePattern = '/^(A|AB|B|BC|C|CD|D|DE|E)[+-]?$/i';

        foreach ($courseBlocks as $code => &$block) {
            $nameParts = [];
            $sks = null;
            $nilai = null;

            foreach ($block['details'] as $detail) {
                $detail = trim($detail);

                if (preg_match($gradePattern, $detail, $m)) {
                    $nilai = strtoupper($m[1]);
                    continue;
                }

                if (is_numeric($detail)) {
                    $val = (int) $detail;
                    if ($val >= 1 && $val <= 24) {
                        $sks = $val;
                        continue;
                    }
                }

                if (preg_match('/^(\d{1,2})\s+([A-E][+-]?)$/i', $detail, $m)) {
                    $sks = (int) $m[1];
                    $nilai = strtoupper($m[2]);
                    continue;
                }

                if (strlen($detail) > 2 && !preg_match('/^\d/', $detail)) {
                    $clean = preg_replace('/\s+(AB|BC|CD|DE|AB|BC|CD|DE)[+-]?\s*$/i', '', $detail);
                    $clean = trim(preg_replace('/\s+/', ' ', $clean));
                    if (strlen($clean) > 2) {
                        $nameParts[] = $clean;
                    }
                }
            }

            $block['name'] = implode(' ', $nameParts);
            $block['name'] = trim(preg_replace('/\s+/', ' ', $block['name']));
            $block['sks'] = $sks;
            $block['nilai'] = $nilai;
        }
        unset($block);

        $courses = [];
        $usedNames = [];

        $sortedCodes = array_keys($courseBlocks);
        for ($i = 0; $i < count($sortedCodes); $i++) {
            $code = $sortedCodes[$i];
            $block = $courseBlocks[$code];

            if (empty($block['name'])) continue;
            if (strlen($block['name']) < 4) continue;
            if ($this->isHeaderOrGarbage($block['name'])) continue;

            $name = $block['name'];
            $sks = $block['sks'];
            $nilai = $block['nilai'];

            if ($i + 1 < count($sortedCodes)) {
                $nextCode = $sortedCodes[$i + 1];
                $nextBlock = $courseBlocks[$nextCode];
                if (empty($nextBlock['name']) && $nilai === null && $nextBlock['nilai'] !== null) {
                    $nilai = $nextBlock['nilai'];
                }
                if (empty($nextBlock['name']) && $sks === null && $nextBlock['sks'] !== null) {
                    $sks = $nextBlock['sks'];
                }
            }

            $key = str_replace(' ', '', strtoupper($name));
            if (isset($usedNames[$key])) continue;
            $usedNames[$key] = true;

            $courses[] = [
                'nama_mata_kuliah' => $name,
                'sks' => $sks,
                'nilai' => $nilai,
            ];
        }

        return $courses;
    }

    private function isHeaderOrGarbage(string $name): bool
    {
        $garbage = [
            'MATA KULIAH', 'MATAKULIAI', 'CREDIT', 'GRADE',
            'NILAI', 'SKS', 'MILA', 'SKO', 'HURUF', 'MUTU', 'ANGKA',
            'MENYATAKAN', 'BAHWA',
        ];

        $upper = strtoupper(trim($name));
        if (in_array($upper, $garbage)) return true;
        if (strlen($name) < 4) return true;
        if (preg_match('/^[0-9\s]+$/', $name)) return true;
        if (preg_match('/^[A-Z0-9]+-\d+$/', $name)) return true;

        return false;
    }
}
