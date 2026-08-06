<?php










class Excel
{
    





    public static function read(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("File tidak ditemukan: {$filePath}");
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new Exception("Gagal membuka file Excel (bukan ZIP valid)");
        }

        
        $sharedStrings = [];
        if (($content = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $xml = simplexml_load_string($content);
            if ($xml) {
                foreach ($xml->si as $si) {
                    $sharedStrings[] = (string)$si->t;
                }
            }
        }

        
        $sheetNames = [];
        if (($content = $zip->getFromName('xl/workbook.xml')) !== false) {
            $xml = simplexml_load_string($content);
            if ($xml) {
                $ns = $xml->getNamespaces(true);
                $workbookNs = $ns[''] ?? '';
                foreach ($xml->sheets->sheet as $sheet) {
                    $attrs = $sheet->attributes($workbookNs);
                    $sheetNames[] = (string)$sheet['name'];
                }
            }
        }

        $result = [];

        
        foreach ($sheetNames as $index => $name) {
            $sheetFile = 'xl/worksheets/sheet' . ($index + 1) . '.xml';
            if (($content = $zip->getFromName($sheetFile)) === false) {
                continue;
            }

            $xml = simplexml_load_string($content);
            if (!$xml) continue;

            $rows = [];
            $ns = $xml->getNamespaces(true);
            $spreadsheetNs = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

            foreach ($xml->sheetData->row as $row) {
                $rowData = [];
                foreach ($row->c as $cell) {
                    $cellRef = (string)$cell['r'];
                    $cellType = (string)$cell['t'];
                    $value = '';

                    if ($cell->v !== null) {
                        $value = (string)$cell->v;
                        if ($cellType === 's' && isset($sharedStrings[(int)$value])) {
                            $value = $sharedStrings[(int)$value];
                        }
                    }

                    
                    preg_match('/([A-Z]+)(\d+)/', $cellRef, $matches);
                    $colLetter = $matches[1] ?? '';
                    $rowData[$colLetter] = $value;
                }
                
                if (!empty($rowData)) {
                    ksort($rowData); 
                    $rows[] = array_values($rowData);
                }
            }

            $result[$name] = $rows;
        }

        $zip->close();
        return $result;
    }

    





    public static function readAsAssoc(string $filePath): array
    {
        $data = self::read($filePath);
        $result = [];

        foreach ($data as $sheetName => $rows) {
            if (empty($rows)) continue;

            
            $headerRow = null;
            $headerIndex = 0;
            foreach ($rows as $i => $row) {
                $filtered = array_filter($row, fn($v) => $v !== '');
                if (!empty($filtered)) {
                    $headerRow = $row;
                    $headerIndex = $i;
                    break;
                }
            }

            if (!$headerRow) continue;

            $assocRows = [];
            for ($i = $headerIndex + 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $assocRow = [];
                $isEmpty = true;
                
                foreach ($headerRow as $colIndex => $headerName) {
                    $value = $row[$colIndex] ?? '';
                    $assocRow[trim($headerName)] = $value;
                    if ($value !== '') $isEmpty = false;
                }

                if (!$isEmpty) {
                    $assocRows[] = $assocRow;
                }
            }

            $result[$sheetName] = $assocRows;
        }

        return $result;
    }

    






    public static function write(array $sheets, string $filePath): bool
    {
        if (empty($sheets)) {
            throw new Exception("Tidak ada data untuk ditulis");
        }

        
        $sharedStrings = [];
        $sharedIndex = [];
        $sheetXmls = [];
        $sheetNames = [];

        $getSharedIndex = function($value) use (&$sharedStrings, &$sharedIndex) {
            $key = md5($value);
            if (!isset($sharedIndex[$key])) {
                $sharedIndex[$key] = count($sharedStrings);
                $sharedStrings[] = $value;
            }
            return $sharedIndex[$key];
        };

        $colLetter = function($index) {
            $letters = '';
            while ($index >= 0) {
                $letters = chr(65 + ($index % 26)) . $letters;
                $index = (int)($index / 26) - 1;
            }
            return $letters;
        };

        foreach ($sheets as $sheetName => $rows) {
            $sheetNames[] = $sheetName;
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"></worksheet>');
            $sheetData = $xml->addChild('sheetData');

            if (empty($rows)) {
                $sheetXmls[] = $xml->asXML();
                continue;
            }

            
            $headers = array_keys($rows[0]);

            foreach ($rows as $rowIndex => $row) {
                $rowElement = $sheetData->addChild('row');
                $rowElement['r'] = $rowIndex + 1;

                foreach ($headers as $colIndex => $header) {
                    $value = $row[$header] ?? '';
                    $cellRef = $colLetter($colIndex) . ($rowIndex + 1);
                    
                    $cell = $rowElement->addChild('c');
                    $cell['r'] = $cellRef;

                    if (is_numeric($value) && !preg_match('/^0\d/', $value)) {
                        $cell['t'] = 'n';
                        $cell->addChild('v', $value);
                    } else {
                        $cell['t'] = 's';
                        $idx = $getSharedIndex($value);
                        $cell->addChild('v', $idx);
                    }
                }
            }

            $sheetXmls[] = $xml->asXML();
        }

        
        $sstXml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"></sst>');
        $sstXml['count'] = count($sharedStrings);
        $sstXml['uniqueCount'] = count($sharedStrings);
        foreach ($sharedStrings as $ss) {
            $si = $sstXml->addChild('si');
            $si->addChild('t', htmlspecialchars($ss));
            
            $si->t['xml:space'] = 'preserve';
        }

        
        $wbXml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"></workbook>');
        $sheets = $wbXml->addChild('sheets');
        foreach ($sheetNames as $i => $name) {
            $sheet = $sheets->addChild('sheet');
            $sheet['name'] = $name;
            $sheet['sheetId'] = $i + 1;
            $sheet['r:id'] = 'rId' . ($i + 1);
        }

        
        $relsXml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>');
        $relsXml->addChild('Relationship', '')->addAttribute('Id', 'rId1');
        $relsXml->Relationship[0]['Type'] = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument';
        $relsXml->Relationship[0]['Target'] = 'xl/workbook.xml';

        
        $zip = new ZipArchive();
        if ($zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Gagal membuat file Excel");
        }

        
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>';
foreach ($sheetNames as $i => $name) {
    $contentTypes .= "\n" . '    <Override PartName="/xl/worksheets/sheet' . ($i + 1) . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
}
$contentTypes .= "\n" . '</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        
        $zip->addFromString('_rels/.rels', $relsXml->asXML());

        
        $wbRels = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>');
        foreach ($sheetNames as $i => $name) {
            $rel = $wbRels->addChild('Relationship');
            $rel['Id'] = 'rId' . ($i + 1);
            $rel['Type'] = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet';
            $rel['Target'] = 'worksheets/sheet' . ($i + 1) . '.xml';
        }
        $ssRel = $wbRels->addChild('Relationship');
        $ssRel['Id'] = 'rId' . (count($sheetNames) + 1);
        $ssRel['Type'] = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings';
        $ssRel['Target'] = 'sharedStrings.xml';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels->asXML());

        
        $zip->addFromString('xl/workbook.xml', $wbXml->asXML());

        
        $zip->addFromString('xl/sharedStrings.xml', $sstXml->asXML());

        
        foreach ($sheetXmls as $i => $sheetXml) {
            $zip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', $sheetXml);
        }

        $zip->close();
        return true;
    }

    





    public static function serialToDate(mixed $serial): string
    {
        if (empty($serial) || !is_numeric($serial)) return '';
        if ($serial < 1) return '';
        
        
        
        $unix = ($serial - 25569) * 86400;
        return gmdate('Y-m-d', $unix);
    }

    


    public static function parseValue(string $value): mixed
    {
        $value = trim($value);
        
        
        if ($value === '') return '';
        
        
        if (is_numeric($value)) {
            
            $num = (float)$value;
            if ($num > 40000 && $num < 60000 && $num == (int)$num) {
                return self::serialToDate($num);
            }
            return $num;
        }
        
        
        if (strtolower($value) === 'true') return true;
        if (strtolower($value) === 'false') return false;
        
        return $value;
    }
}
