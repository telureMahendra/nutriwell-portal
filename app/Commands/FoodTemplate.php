<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Regenerates public/FoodData.xlsx, the sheet staff download before a bulk food
 * upload.
 *
 * The Unit column is a locked dropdown built from foodUnitList(), so the sheet
 * can never offer a unit the importer would reject. Re-run this command after
 * changing the unit vocabulary in app/Helpers/food_unit_helper.php.
 *
 *   php spark food:template
 *      Small starter sheet - headers, dropdown and three example rows.
 *
 *   php spark food:template --source "C:/path/Recipes.xlsx"
 *      Same sheet, but filled with every row of the source file and with the
 *      Unit column pre-filled from each food name.
 *
 *   php spark food:template --source "..." --blank-units
 *      As above but leaves Unit empty for staff to fill from the dropdown.
 */
class FoodTemplate extends BaseCommand
{
    protected $group       = 'Food';
    protected $name        = 'food:template';
    protected $description = 'Regenerate public/FoodData.xlsx with the Unit dropdown.';
    protected $usage       = 'food:template [--source <file.xlsx>] [--blank-units]';
    protected $options     = [
        '--source'      => 'Spreadsheet to copy Food Name / Calories rows from.',
        '--blank-units' => 'Leave the Unit column empty instead of pre-filling it.',
    ];

    /** Spare rows the dropdown covers below the last data row. */
    private const SPARE_ROWS = 500;

    public function run(array $params)
    {
        require APPPATH . 'ThirdParty/phpspreadsheet/vendor/autoload.php';

        $source     = CLI::getOption('source');
        $blankUnits = (bool) CLI::getOption('blank-units');

        if ($source !== null && !is_file($source)) {
            CLI::error('Source file not found: ' . $source);

            return;
        }

        [$rows, $notes] = $source !== null
            ? $this->rowsFromSource($source, $blankUnits)
            : [$this->exampleRows(), []];

        if ($rows === null) {
            return;
        }

        // Served as base_url('public/FoodData.xlsx') - index.php sits at the
        // project root, so the docroot is the root and public/ is a plain folder.
        $target = ROOTPATH . 'public' . DIRECTORY_SEPARATOR . 'FoodData.xlsx';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle('Healthie4U food upload sheet')
            ->setDescription('Food Name, Calories, Unit and optional Food Type. Unit is a dropdown.');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Food');

        // --- header ---
        $sheet->fromArray(['Food Name', 'Calories', 'Unit', 'Food Type (Optional)'], null, 'A1');

        $headerStyle = $sheet->getStyle('A1:D1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F4FC');
        $headerStyle->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // --- data ---
        $sheet->fromArray($rows, null, 'A2', true);

        // Food names are free text; stop Excel reformatting anything that looks
        // like a number or date.
        $sheet->getStyle('A2:A' . (count($rows) + 1))
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // --- Unit dropdown ---
        // Inline list, capped at 255 characters by the format. The 14 labels are
        // well inside that; guard anyway so a future addition fails loudly here
        // rather than producing a silently broken sheet.
        $labels  = foodUnitLabelList();
        $formula = '"' . implode(',', $labels) . '"';
        if (strlen($formula) > 255) {
            CLI::error('Unit list is ' . strlen($formula) . ' chars - too long for an inline Excel list.');
            CLI::write('Put the labels on the Units sheet and point formula1 at that range instead.');

            return;
        }

        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowInputMessage(true);
        $validation->setErrorTitle('Invalid unit');
        $validation->setError('Pick a unit from the dropdown list.');
        $validation->setPromptTitle('Measurement unit');
        $validation->setPrompt('Optional. Pick one of the ' . count($labels) . ' units from the list.');
        $validation->setFormula1($formula);

        $lastValidationRow = count($rows) + 1 + self::SPARE_ROWS;
        $sheet->setDataValidation('C2:C' . $lastValidationRow, $validation);

        foreach (['A' => 62, 'B' => 12, 'C' => 16, 'D' => 22] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:D1');
        $sheet->setSelectedCell('A2');

        // --- reference sheet: what each unit means to the importer ---
        $units = $spreadsheet->createSheet();
        $units->setTitle('Units');
        $units->fromArray(['Unit', 'Icon', 'Stored as'], null, 'A1');
        $units->getStyle('A1:C1')->getFont()->setBold(true);

        $row = 2;
        foreach (foodUnitList() as $code => $unit) {
            $units->fromArray([$unit['label'], $unit['emoji'], $code], null, 'A' . $row);
            $row++;
        }

        $row += 1;
        $units->fromArray(
            ['Leave Unit blank and the unit is worked out from the food name, e.g. "Plain poha, 1 katori" becomes Katori.'],
            null,
            'A' . $row
        );
        foreach ($notes as $note) {
            $row++;
            $units->fromArray([$note], null, 'A' . $row);
        }
        foreach (['A' => 16, 'B' => 8, 'C' => 16] as $column => $width) {
            $units->getColumnDimension($column)->setWidth($width);
        }

        $spreadsheet->setActiveSheetIndex(0);
        (new Xlsx($spreadsheet))->save($target);
        $spreadsheet->disconnectWorksheets();

        CLI::write('Wrote ' . $target, 'green');
        CLI::write('  data rows: ' . number_format(count($rows)));
        CLI::write('  file size: ' . number_format(filesize($target) / 1024, 1) . ' KB');
        CLI::write('  unit dropdown (' . count($labels) . '): ' . implode(', ', $labels));
        CLI::write('  dropdown applies to C2:C' . $lastValidationRow);
        foreach ($notes as $note) {
            CLI::write('  ' . $note);
        }
    }

    /**
     * The three-row starter sheet used when no source file is given.
     */
    private function exampleRows()
    {
        return [
            ['Plain poha, 1 katori', 137, 'Katori', ''],
            ['Chapati, 1', 71, 'Piece', ''],
            ['Buttermilk, 1 glass', 62, 'Glass', ''],
        ];
    }

    /**
     * Read every named row out of a source spreadsheet.
     *
     * @return array [rows, notes] - rows is null on failure.
     */
    private function rowsFromSource($source, $blankUnits)
    {
        $spreadsheet = IOFactory::load($source);
        $sheet       = $spreadsheet->getActiveSheet();
        $highest     = $sheet->getHighestColumn();

        $columns = detectFoodSheetColumns($sheet, $highest);
        if ($columns === null) {
            // No usable header row - assume the common [name, calories] layout.
            $columns = ['name' => 0, 'calories' => 1, 'unit' => null, 'type' => null];
            CLI::write('  no recognisable header row in the source; assuming column A = name, B = calories', 'yellow');
        }

        $rows       = [];
        $derived    = 0;
        $fromSource = 0;
        $fallback   = 0;
        $tooLong    = 0;

        for ($r = 2; $r <= $sheet->getHighestRow(); $r++) {
            $data = $sheet->rangeToArray('A' . $r . ':' . $highest . $r, null, true, false)[0];

            $read = static function ($index) use ($data) {
                return ($index !== null && isset($data[$index])) ? trim((string) $data[$index]) : '';
            };

            $name = $read($columns['name']);
            if ($name === '') {
                continue;
            }

            $calories = $read($columns['calories']);
            $rawUnit  = $read($columns['unit']);
            $type     = $read($columns['type']);

            $unitLabel = '';
            if (!$blankUnits) {
                // Respect a unit already in the source, otherwise work it out
                // from the name - the same precedence the importer uses.
                $code = normalizeFoodUnit($rawUnit);
                if ($code !== null) {
                    $fromSource++;
                } else {
                    $code = deriveFoodUnitFromName($name);
                    $derived++;
                    if ($code === foodUnitDefault()) {
                        $fallback++;
                    }
                }
                $unitLabel = foodUnitLabel($code);
            }

            if (mb_strlen($name) > 50) {
                $tooLong++;
            }

            $rows[] = [$name, is_numeric($calories) ? (float) $calories : $calories, $unitLabel, $type];
        }

        $spreadsheet->disconnectWorksheets();

        if (!$rows) {
            CLI::error('No rows with a food name found in ' . $source);

            return [null, []];
        }

        $notes = ['Generated from ' . basename($source) . ' - ' . number_format(count($rows)) . ' rows.'];
        if (!$blankUnits) {
            $notes[] = 'Unit pre-filled: ' . number_format($fromSource) . ' taken from the source file, '
                . number_format($derived) . ' worked out from the food name.';
            $notes[] = 'Of those, ' . number_format($fallback) . ' had no unit in the name and were set to '
                . foodUnitLabel(foodUnitDefault()) . ' - worth reviewing.';
        }
        if ($tooLong > 0) {
            $notes[] = 'WARNING: ' . number_format($tooLong) . ' names are longer than 50 characters and will be '
                . 'truncated unless food.food_name is widened.';
        }

        return [$rows, $notes];
    }
}
