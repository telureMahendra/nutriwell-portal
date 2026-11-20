<?php
/**
 * Food measurement unit helper.
 *
 * The food catalogue is bulk loaded from Excel where the unit is usually part of
 * the food name ("Plain poha, 1 katori") rather than a column of its own. These
 * helpers give the admin portal and the calorie APIs one shared vocabulary and
 * one shared way to resolve a unit for a food row.
 *
 * resolveFoodUnit() is the only entry point callers should use - it prefers the
 * stored `food.unit` value and falls back to deriving the unit from the name.
 */

if (!function_exists('foodUnitList')) {
    /**
     * The canonical unit vocabulary. Single source of truth for the API, the
     * admin dropdown and the Excel template.
     *
     * @return array code => ['label' => string, 'emoji' => string]
     */
    function foodUnitList()
    {
        return [
            'katori'     => ['label' => 'Katori',     'emoji' => '🍚'],
            'bowl'       => ['label' => 'Bowl',       'emoji' => '🥣'],
            'cup'        => ['label' => 'Cup',        'emoji' => '🥛'],
            'teacup'     => ['label' => 'Teacup',     'emoji' => '☕'],
            'glass'      => ['label' => 'Glass',      'emoji' => '🥛'],
            'plate'      => ['label' => 'Plate',      'emoji' => '🍽'],
            'piece'      => ['label' => 'Piece',      'emoji' => '🍢'],
            'slice'      => ['label' => 'Slice',      'emoji' => '🍞'],
            'scoop'      => ['label' => 'Scoop',      'emoji' => '🍨'],
            'tablespoon' => ['label' => 'Tablespoon', 'emoji' => '🥄'],
            'teaspoon'   => ['label' => 'Teaspoon',   'emoji' => '🥄'],
            'packet'     => ['label' => 'Packet',     'emoji' => '📦'],
            'bar'        => ['label' => 'Bar',        'emoji' => '🍫'],
            'serving'    => ['label' => 'Serving',    'emoji' => '🍛'],
        ];
    }
}

if (!function_exists('foodUnitDefault')) {
    /**
     * The code used when nothing else can be worked out.
     */
    function foodUnitDefault()
    {
        return 'serving';
    }
}

if (!function_exists('foodUnitAliases')) {
    /**
     * Every spelling that may appear in an uploaded sheet or inside a food name,
     * mapped to its canonical code. Plurals are listed explicitly rather than
     * stripped, so "glass" is never mangled into "glas".
     *
     * @return array alias => code
     */
    function foodUnitAliases()
    {
        $aliases = [
            // katori
            'katori' => 'katori', 'katoris' => 'katori', 'katory' => 'katori',
            // bowl
            'bowl' => 'bowl', 'bowls' => 'bowl', 'ramen' => 'bowl',
            // cup
            'cup' => 'cup', 'cups' => 'cup',
            // teacup
            'teacup' => 'teacup', 'teacups' => 'teacup',
            // glass
            'glass' => 'glass', 'glasses' => 'glass',
            // plate
            'plate' => 'plate', 'plates' => 'plate',
            // piece - countable items
            'piece' => 'piece', 'pieces' => 'piece', 'pc' => 'piece', 'pcs' => 'piece',
            'puri' => 'piece', 'puris' => 'piece', 'idli' => 'piece', 'idlis' => 'piece',
            'pav' => 'piece', 'pavs' => 'piece', 'bun' => 'piece', 'buns' => 'piece',
            'seekh' => 'piece', 'cube' => 'piece', 'cubes' => 'piece',
            'square' => 'piece', 'squares' => 'piece', 'sqaure' => 'piece',
            'triangle' => 'piece', 'triangles' => 'piece', 'pizza' => 'piece',
            'khulcha' => 'piece', 'paratha' => 'piece', 'roti' => 'piece',
            'chapati' => 'piece', 'chapatti' => 'piece',
            // slice
            'slice' => 'slice', 'slices' => 'slice',
            // scoop
            'scoop' => 'scoop', 'scoops' => 'scoop',
            // tablespoon
            'tablespoon' => 'tablespoon', 'tablespoons' => 'tablespoon',
            'tbsp' => 'tablespoon', 'tbsps' => 'tablespoon', 'tbs' => 'tablespoon',
            // teaspoon
            'teaspoon' => 'teaspoon', 'teaspoons' => 'teaspoon',
            'tsp' => 'teaspoon', 'tsps' => 'teaspoon',
            // packet
            'packet' => 'packet', 'packets' => 'packet', 'pack' => 'packet', 'packs' => 'packet',
            // bar
            'bar' => 'bar', 'bars' => 'bar',
            // serving
            'serving' => 'serving', 'servings' => 'serving', 'serve' => 'serving',
            'serves' => 'serving', 'handful' => 'serving',
        ];

        // Every canonical code is always accepted as-is.
        foreach (array_keys(foodUnitList()) as $code) {
            $aliases[$code] = $code;
        }

        return $aliases;
    }
}

if (!function_exists('foodUnitSizeWords')) {
    /**
     * Words that describe a size or a count, not a unit. Stripped before lookup
     * so "1 medium slice" resolves to slice and "1 small" is still recognised as
     * a countable piece.
     */
    function foodUnitSizeWords()
    {
        return [
            'small', 'medium', 'med', 'large', 'big', 'mini', 'regular', 'extra',
            'long', 'half', 'full', 'one', 'two', 'three', 'of', 'a', 'an',
        ];
    }
}

if (!function_exists('foodUnitCleanText')) {
    /**
     * Lowercase, drop digits/fractions/punctuation and collapse whitespace.
     */
    function foodUnitCleanText($text)
    {
        $text = strtolower(trim((string) $text));
        if ($text === '') {
            return '';
        }
        // Fractions and digits carry the count, never the unit.
        $text = str_replace(['½', '¼', '¾', '⅓', '⅔'], ' ', $text);
        $text = preg_replace('/[^a-z]+/', ' ', $text);

        return trim(preg_replace('/\s+/', ' ', $text));
    }
}

if (!function_exists('normalizeFoodUnit')) {
    /**
     * Free text -> canonical unit code, or null when nothing is recognised.
     *
     * Accepts anything staff might type in the sheet or the admin form:
     * "Katori", " bowl ", "1 Katori", "pcs", "medium slice", "ramen bowl".
     * Scans right to left so the trailing word wins, which is where the unit
     * sits in every real example ("Black chana masala 1 katori").
     */
    function normalizeFoodUnit($raw)
    {
        $clean = foodUnitCleanText($raw);
        if ($clean === '') {
            return null;
        }

        $aliases = foodUnitAliases();
        if (isset($aliases[$clean])) {
            return $aliases[$clean];
        }

        foreach (array_reverse(explode(' ', $clean)) as $word) {
            if (isset($aliases[$word])) {
                return $aliases[$word];
            }
        }

        return null;
    }
}

if (!function_exists('foodUnitIsCountable')) {
    /**
     * True when a name tail is a bare count, optionally with a size word:
     * "1", "2", "1 small", "1 medium". Those rows are countable pieces
     * ("Poha Idli, 1", "Rava Dosa, 1 small").
     */
    function foodUnitIsCountable($tail)
    {
        if (!preg_match('/\d/', (string) $tail)) {
            return false;
        }

        $clean = foodUnitCleanText($tail);
        if ($clean === '') {
            return true;
        }

        $sizeWords = foodUnitSizeWords();
        foreach (explode(' ', $clean) as $word) {
            if (!in_array($word, $sizeWords, true)) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('deriveFoodUnitFromName')) {
    /**
     * Work the unit out of a food name. Used for rows whose `unit` column was
     * never filled in - the bulk of the existing catalogue.
     *
     * Never returns null; unrecognised names fall back to the default unit.
     */
    function deriveFoodUnitFromName($food_name)
    {
        $food_name = (string) $food_name;
        if (trim($food_name) === '') {
            return foodUnitDefault();
        }

        // 1. The text after the last comma is where the measure lives.
        if (strpos($food_name, ',') !== false) {
            $parts = explode(',', $food_name);
            $tail  = trim(end($parts));

            if ($tail !== '') {
                $code = normalizeFoodUnit($tail);
                if ($code !== null) {
                    return $code;
                }
                // 2. A bare count means a countable item.
                if (foodUnitIsCountable($tail)) {
                    return 'piece';
                }
            }
        }

        // 3. No comma, or the tail told us nothing - look across the whole name.
        $code = normalizeFoodUnit($food_name);
        if ($code !== null) {
            return $code;
        }

        return foodUnitDefault();
    }
}

if (!function_exists('resolveFoodUnit')) {
    /**
     * The unit for a food row. A stored value always wins; otherwise the unit is
     * derived from the name. Always returns a valid canonical code.
     */
    function resolveFoodUnit($stored_unit, $food_name)
    {
        $code = normalizeFoodUnit($stored_unit);
        if ($code !== null) {
            return $code;
        }

        return deriveFoodUnitFromName($food_name);
    }
}

if (!function_exists('foodUnitIsStored')) {
    /**
     * Whether a row carries a real stored unit, as opposed to one we derived.
     * Lets the admin list flag rows that still need a unit set.
     */
    function foodUnitIsStored($stored_unit)
    {
        return normalizeFoodUnit($stored_unit) !== null;
    }
}

if (!function_exists('foodUnitLabel')) {
    /**
     * Display text for a canonical code.
     */
    function foodUnitLabel($code)
    {
        $units = foodUnitList();

        return isset($units[$code]) ? $units[$code]['label'] : $units[foodUnitDefault()]['label'];
    }
}

if (!function_exists('foodUnitEmoji')) {
    /**
     * Emoji for a canonical code. Lets a client show a sensible icon before it
     * has artwork of its own.
     */
    function foodUnitEmoji($code)
    {
        $units = foodUnitList();

        return isset($units[$code]) ? $units[$code]['emoji'] : $units[foodUnitDefault()]['emoji'];
    }
}

if (!function_exists('foodUnitOptions')) {
    /**
     * <option> markup for the admin unit dropdowns, matching how the food type
     * options are built in Admin\Food.
     */
    function foodUnitOptions($selected = null, $placeholder = 'Select Unit')
    {
        $selected = normalizeFoodUnit($selected);
        $html     = '<option value="">' . $placeholder . '</option>';

        foreach (foodUnitList() as $code => $unit) {
            $sel = ($selected === $code) ? ' selected="selected"' : '';
            $html .= '<option value="' . $code . '"' . $sel . '>' . $unit['emoji'] . ' ' . $unit['label'] . '</option>';
        }

        return $html;
    }
}

if (!function_exists('foodUnitLabelList')) {
    /**
     * Just the labels, in order. Used for the Excel dropdown validation list.
     *
     * @return array
     */
    function foodUnitLabelList()
    {
        return array_column(foodUnitList(), 'label');
    }
}

if (!function_exists('detectFoodSheetColumns')) {
    /**
     * Map the header row of a food spreadsheet to column indexes, so the order
     * of the columns in an uploaded sheet does not matter.
     *
     * Shared by the importer and the template generator.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param string $highestColumn
     *
     * @return array|null ['name' => int, 'calories' => int, 'unit' => int|null, 'type' => int|null]
     *                    or null when the header row cannot be understood, in
     *                    which case the caller should guess by position.
     */
    function detectFoodSheetColumns($sheet, $highestColumn)
    {
        $headerAliases = [
            'name' => ['food name', 'foodname', 'food', 'name', 'item', 'item name', 'recipe', 'recipe name'],
            'calories' => ['calories', 'calorie', 'kcal', 'cal', 'energy'],
            'unit' => ['unit', 'units', 'measure', 'measurement', 'measurement unit', 'serving unit', 'quantity unit'],
            'type' => ['food type', 'food type (optional)', 'foodtype', 'type', 'meal type', 'category'],
        ];

        $header  = $sheet->rangeToArray('A1:' . $highestColumn . '1', null, true, false)[0];
        $columns = ['name' => null, 'calories' => null, 'unit' => null, 'type' => null];

        foreach ($header as $index => $value) {
            $label = strtolower(trim((string) $value));
            if ($label === '') {
                continue;
            }
            foreach ($headerAliases as $key => $aliases) {
                if ($columns[$key] === null && in_array($label, $aliases, true)) {
                    $columns[$key] = $index;
                    break;
                }
            }
        }

        // Name and calories are the minimum needed to trust the header row.
        if ($columns['name'] === null || $columns['calories'] === null) {
            return null;
        }

        return $columns;
    }
}

if (!function_exists('attachFoodUnit')) {
    /**
     * Add the unit fields to a food row on its way out of an API.
     *
     * Additive: `unit` is overwritten with the resolved code so it is never
     * empty, and `unit_label` / `unit_icon` are appended. No existing key is
     * renamed or removed.
     *
     * @param object $row a food row carrying at least food_name
     */
    function attachFoodUnit($row)
    {
        if (!is_object($row)) {
            return $row;
        }

        $code = resolveFoodUnit($row->unit ?? null, $row->food_name ?? '');

        $row->unit       = $code;
        $row->unit_label = foodUnitLabel($code);
        $row->unit_icon  = foodUnitEmoji($code);

        return $row;
    }
}

if (!function_exists('attachFoodUnitList')) {
    /**
     * attachFoodUnit() over a list of rows.
     *
     * @param array $rows
     */
    function attachFoodUnitList($rows)
    {
        if (empty($rows) || !is_array($rows)) {
            return $rows;
        }

        foreach ($rows as $row) {
            attachFoodUnit($row);
        }

        return $rows;
    }
}
