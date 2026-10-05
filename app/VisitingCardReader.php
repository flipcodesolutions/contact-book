<?php

namespace App;

class VisitingCardReader
{
    private $cityHint = '';

    private $addressHint = '';

    private $otherNumbers = [];

    /**
     * Turn the text read from a visiting card into lead fields.
     * $countries is a list of country records with id, name, iso2, and dial_code.
     */
    public function read(string $text, $countries): array
    {
        $this->cityHint = '';
        $this->addressHint = '';
        $this->otherNumbers = [];

        $lines = $this->lines($text);
        $used = [];
        $this->cityHint = $this->findCityFromPin($lines);

        $email = $this->findEmail($lines, $used);
        $website = $this->findWebsite($lines, $used);
        $country = $this->findCountry($lines, $used, $countries);
        $phone = $this->findPhone($lines, $used, $countries, $country);

        if ($phone['international']) {
            $fromCode = $this->countryFromDialCode($phone['code'], $countries);

            if ($fromCode) {
                $country = $fromCode;
            }
        }

        $workDepartment = $this->findWorkDepartment($lines, $used);
        $jobTitle = $this->findJobTitle($lines, $used);
        $address = $this->findAddress($lines, $used);
        $company = $this->findCompany($lines, $used);
        $name = $this->findName($lines, $used, $jobTitle);
        $company = $company !== '' ? $company : $this->findFallbackCompany($lines, $used);
        $city = $this->findCity($lines, $used);

        return [
            'full_name' => $this->cut($name, 120),
            'phone_country_code' => $phone['code'],
            'phone_number' => $phone['number'],
            'email' => $this->cut($email, 190),
            'website' => $this->cut($website, 190),
            'company_name' => $this->cut($company, 150),
            'job_title' => $this->cut($jobTitle, 120),
            'work_department' => $this->cut($workDepartment, 120),
            'address' => $this->cut($address, 500),
            'city' => $this->cut($city, 100),
            'country_id' => $country ? (string) $country->id : '',
            'notes' => $this->cut($this->remainingNotes($lines, $used), 2000),
        ];
    }

    private function findCityFromPin(array $lines): string
    {
        foreach ($lines as $line) {
            if (preg_match('/\b([A-Za-z]{3,})\s*[-–]\s*\d{6}\b/u', $line, $match)) {
                return ucwords(strtolower($match[1]));
            }
        }

        return '';
    }

    private function lines(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $rows = explode("\n", $text);
        $lines = [];

        foreach ($rows as $row) {
            $line = preg_replace('/\s+/', ' ', trim($row));
            $line = trim($line, " \t-|•·*");

            if ($line === '' || strlen($line) < 2) {
                continue;
            }

            $lines[] = $line;
        }

        return $lines;
    }

    private function findEmail(array &$lines, array &$used): string
    {
        foreach ($lines as $index => $line) {
            if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $line, $match)) {
                $this->consume($lines, $used, $index, $match[0]);

                return strtolower($match[0]);
            }
        }

        return '';
    }

    private function findWebsite(array &$lines, array &$used): string
    {
        $found = [];

        foreach ($lines as $index => $line) {
            if (isset($used[$index])) {
                continue;
            }

            $raw = '';

            if (preg_match('/https?:\/\/[^\s]+/i', $line, $match)) {
                $raw = $match[0];
            } elseif (preg_match('/\bwww\.[^\s]+/i', $line, $match)) {
                $raw = $match[0];
            } elseif (preg_match('/^[A-Z0-9.-]+\.(com|co|net|org|in|au|uk|ae|io|biz|info|ccrm|corn)(\.[A-Z]{2})?$/i', $line, $match)) {
                $raw = $match[0];
            }

            if ($raw === '') {
                continue;
            }

            $website = rtrim($raw, '.,)');
            $website = preg_replace('/\.(ccrm|corn|c0m|ccm)$/i', '.com', $website);

            if (stripos($website, 'http') !== 0) {
                $website = 'https://'.$website;
            }

            $found[] = [
                'index' => $index,
                'raw' => $raw,
                'website' => $website,
                'score' => preg_match('/\.(com|co\.in|co|net|org|in|au|uk|ae|io)(\/|$)/i', $website) ? 2 : 1,
            ];
        }

        if (count($found) === 0) {
            return '';
        }

        $best = $found[0];

        foreach ($found as $site) {
            if ($site['score'] > $best['score']) {
                $best = $site;
            }
        }

        foreach ($found as $site) {
            $this->consume($lines, $used, $site['index'], $site['raw']);
        }

        return $best['website'];
    }

    private function findCountry(array &$lines, array &$used, $countries)
    {
        $aliases = [
            'uk' => 'GB',
            'u.k.' => 'GB',
            'great britain' => 'GB',
            'usa' => 'US',
            'u.s.a.' => 'US',
            'united states of america' => 'US',
            'uae' => 'AE',
            'u.a.e.' => 'AE',
        ];

        foreach ($lines as $index => $line) {
            if (isset($used[$index])) {
                continue;
            }

            foreach ($aliases as $alias => $iso2) {
                if (! $this->lineNamesPlace($line, $alias)) {
                    continue;
                }

                $country = $this->countryByIso($countries, $iso2);

                if ($country) {
                    $this->splitPlaceLine($lines, $used, $index, $line, $alias);

                    return $country;
                }
            }

            foreach ($countries as $country) {
                if (! $this->lineNamesPlace($line, $country->name)) {
                    continue;
                }

                $this->splitPlaceLine($lines, $used, $index, $line, $country->name);

                return $country;
            }
        }

        return null;
    }

    private function findPhone(array &$lines, array &$used, $countries, $country): array
    {
        $raws = $this->phoneRaws($lines);
        $parsed = [];
        $dialCountry = $country;

        foreach ($raws as $raw) {
            $item = $this->parsePhone($raw['raw'], $countries, null);

            if ($item === null || ! $item['international']) {
                continue;
            }

            $item['index'] = $raw['index'];
            $item['raw'] = $raw['raw'];
            $parsed[] = $item;
            $fromCode = $this->countryFromDialCode($item['code'], $countries);

            if ($fromCode) {
                $dialCountry = $fromCode;
            }
        }

        foreach ($raws as $raw) {
            if ($this->alreadyParsed($parsed, $raw['raw'])) {
                continue;
            }

            $item = $this->parsePhone($raw['raw'], $countries, $dialCountry);

            if ($item === null) {
                continue;
            }

            $item['index'] = $raw['index'];
            $item['raw'] = $raw['raw'];
            $parsed[] = $item;
        }

        if (count($parsed) === 0) {
            return ['code' => '', 'number' => '', 'international' => false];
        }

        $primary = $parsed[0];

        foreach ($parsed as $item) {
            if ($item['international'] && ! $primary['international']) {
                $primary = $item;
            }
        }

        foreach ($parsed as $item) {
            $this->consume($lines, $used, $item['index'], $item['raw']);

            if ($item['number'] === $primary['number'] && $item['code'] === $primary['code']) {
                continue;
            }

            $this->otherNumbers[] = trim($item['code'].' '.$item['number']);
        }

        return [
            'code' => $primary['code'],
            'number' => $primary['number'],
            'international' => $primary['international'],
        ];
    }

    private function phoneRaws(array $lines): array
    {
        $raws = [];

        foreach ($lines as $index => $line) {
            if (! preg_match_all('/(?:\+|00)?\d[\d\s().\-]{5,}\d/', $line, $matches)) {
                continue;
            }

            foreach ($matches[0] as $raw) {
                $raws[] = [
                    'index' => $index,
                    'raw' => $raw,
                ];
            }
        }

        return $raws;
    }

    private function alreadyParsed(array $parsed, string $raw): bool
    {
        foreach ($parsed as $item) {
            if ($item['raw'] === $raw) {
                return true;
            }
        }

        return false;
    }

    private function countryFromDialCode(string $dialCode, $countries)
    {
        if ($dialCode === '') {
            return null;
        }

        $found = null;

        foreach ($countries as $country) {
            if ($country->dial_code !== $dialCode) {
                continue;
            }

            if ($found !== null) {
                return null;
            }

            $found = $country;
        }

        return $found;
    }

    private function lineNamesPlace(string $line, string $label): bool
    {
        $line = trim($line, ' .');

        if (strcasecmp($line, $label) === 0) {
            return true;
        }

        return (bool) preg_match('/^.{1,40},\s*'.preg_quote($label, '/').'$/i', $line);
    }

    private function findWorkDepartment(array &$lines, array &$used): string
    {
        foreach ($lines as $index => $line) {
            if (isset($used[$index])) {
                continue;
            }

            if (preg_match('/\b(department|division)\b/i', $line) && strlen($line) <= 120) {
                $used[$index] = true;

                return $line;
            }
        }

        return '';
    }

    private function findJobTitle(array &$lines, array &$used): string
    {
        $words = [
            'director', 'manager', 'executive', 'consultant', 'engineer', 'designer',
            'owner', 'founder', 'partner', 'president', 'officer', 'buyer',
            'accountant', 'proprietor', 'chairman', 'specialist', 'coordinator',
            'representative', 'supervisor', 'head',
        ];

        return $this->findByWords($lines, $used, $words, 80);
    }

    private function findAddress(array &$lines, array &$used): string
    {
        if ($this->addressHint !== '') {
            return $this->addressHint;
        }

        $parts = [];

        foreach ($lines as $index => $line) {
            if (isset($used[$index])) {
                continue;
            }

            if ($this->isAddress($line)) {
                $used[$index] = true;
                $parts[] = rtrim($line, ' ,.');
            }
        }

        return implode(', ', $parts);
    }

    private function findCompany(array &$lines, array &$used): string
    {
        $words = [
            'ltd', 'limited', 'pvt', 'private', 'llc', 'inc', 'gmbh', 'corp',
            'company', 'co.', 'enterprises', 'industries', 'tiles', 'surfaces',
            'homes', 'group', 'studio', 'trading', 'solutions', 'international',
            'associates', 'builders', 'exports', 'tour', 'tours', 'travel', 'travels',
            'services',
        ];

        return $this->findByWords($lines, $used, $words, 150);
    }

    /**
     * The name is the person line nearest above the job title.
     * When there is no title, it is the first person line.
     */
    private function findName(array &$lines, array &$used, string $jobTitle): string
    {
        $titleIndex = null;

        if ($jobTitle !== '') {
            foreach ($lines as $index => $line) {
                if ($line === $jobTitle) {
                    $titleIndex = $index;
                    break;
                }
            }
        }

        $chosen = null;

        foreach ($lines as $index => $line) {
            if (isset($used[$index]) || ! $this->looksLikePerson($line)) {
                continue;
            }

            if ($titleIndex !== null && $index < $titleIndex) {
                $chosen = $index;
            }

            if ($titleIndex === null && $chosen === null) {
                $chosen = $index;
            }
        }

        if ($chosen === null) {
            return '';
        }

        $used[$chosen] = true;

        return $lines[$chosen];
    }

    private function findFallbackCompany(array &$lines, array &$used): string
    {
        foreach ($lines as $index => $line) {
            if (isset($used[$index]) || ! $this->looksLikePerson($line)) {
                continue;
            }

            $used[$index] = true;

            return $line;
        }

        return '';
    }

    private function findCity(array &$lines, array &$used): string
    {
        if ($this->cityHint !== '') {
            return $this->cityHint;
        }

        foreach ($lines as $index => $line) {
            if (isset($used[$index])) {
                continue;
            }

            if ($this->looksLikeCity($line)) {
                $used[$index] = true;

                return $line;
            }
        }

        return '';
    }

    private function remainingNotes(array $lines, array $used): string
    {
        $notes = [];

        foreach ($this->otherNumbers as $number) {
            $notes[] = 'Other number: '.$number;
        }

        foreach ($lines as $index => $line) {
            if (isset($used[$index])) {
                continue;
            }

            if (! preg_match('/[A-Za-z]{4,}/', $line)) {
                continue;
            }

            if (preg_match('/^[A-Z]{2,12}$/', $line)) {
                continue;
            }

            $notes[] = $line;
        }

        return implode("\n", $notes);
    }

    private function parsePhone(string $raw, $countries, $country): ?array
    {
        $digits = preg_replace('/\D/', '', $raw);
        $hadInternationalPrefix = strpos(trim($raw), '+') === 0 || strpos($digits, '00') === 0;

        if (strpos($digits, '00') === 0) {
            $digits = substr($digits, 2);
        }

        if ($hadInternationalPrefix || strlen($digits) > 10) {
            foreach ($this->dialCodes($countries) as $code) {
                if ($code['digits'] === '' || strpos($digits, $code['digits']) !== 0) {
                    continue;
                }

                $national = substr($digits, strlen($code['digits']));

                if (strlen($national) >= 6 && strlen($national) <= 15) {
                    return [
                        'code' => $code['code'],
                        'number' => $national,
                        'international' => true,
                    ];
                }
            }
        }

        $national = $digits;

        if (strpos($national, '0') === 0) {
            $national = substr($national, 1);
        }

        if ($country && strlen($national) >= 6 && strlen($national) <= 15) {
            return [
                'code' => $country->dial_code,
                'number' => $national,
                'international' => false,
            ];
        }

        return null;
    }

    private function dialCodes($countries): array
    {
        $codes = [];

        foreach ($countries as $country) {
            $codes[] = [
                'code' => $country->dial_code,
                'digits' => preg_replace('/\D/', '', (string) $country->dial_code),
            ];
        }

        $count = count($codes);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                if (strlen($codes[$j]['digits']) > strlen($codes[$i]['digits'])) {
                    $swap = $codes[$i];
                    $codes[$i] = $codes[$j];
                    $codes[$j] = $swap;
                }
            }
        }

        return $codes;
    }

    private function countryByIso($countries, string $iso2)
    {
        foreach ($countries as $country) {
            if (strtoupper($country->iso2) === strtoupper($iso2)) {
                return $country;
            }
        }

        return null;
    }

    private function splitPlaceLine(array &$lines, array &$used, $index, string $line, string $label): void
    {
        $used[$index] = true;

        if (! preg_match('/^(.*?)\b'.preg_quote($label, '/').'\b/i', $line, $match)) {
            return;
        }

        $before = trim($match[1], " \t,:;-");

        if ($before === '' || strlen($before) > 120) {
            return;
        }

        if ($this->isAddress($before)) {
            $this->addressHint = $before;

            return;
        }

        $this->cityHint = $before;
    }

    private function findByWords(array &$lines, array &$used, array $words, int $maxLength): string
    {
        foreach ($lines as $index => $line) {
            if (isset($used[$index]) || strlen($line) > $maxLength) {
                continue;
            }

            foreach ($words as $word) {
                if (preg_match('/\b'.preg_quote($word, '/').'\b/i', $line)) {
                    $used[$index] = true;

                    return $line;
                }
            }
        }

        return '';
    }

    private function isAddress(string $line): bool
    {
        if (preg_match('/^\d+\s*,/', $line) || preg_match('/\b\d{6}\b/', $line)) {
            return true;
        }

        return (bool) preg_match('/\b(street|st|road|rd|lane|avenue|ave|plot|floor|building|suite|block|sector|nagar|marg|drive|way|highway|industrial|apartment|appartment|near|garden|society|opposite|complex)\b/i', $line);
    }

    private function looksLikePerson(string $line): bool
    {
        if ($this->looksLikeCompany($line) || preg_match('/\d|@/', $line) || strlen($line) > 60) {
            return false;
        }

        if (strpos($line, '.') !== false) {
            $compact = preg_replace('/\s+/', '', $line);

            if (preg_match("/^[A-Z](?:\.[A-Z])+\.?[A-Za-z][A-Za-z'-]{2,}$/", $compact)) {
                return true;
            }
        }

        $words = preg_split('/\s+/', $line);

        if (count($words) < 2 || count($words) > 4) {
            return false;
        }

        $hasLongWord = false;

        foreach ($words as $word) {
            if (! preg_match("/^[A-Za-z][A-Za-z.'-]*$/", $word)) {
                return false;
            }

            if (strlen(trim($word, '.')) >= 3) {
                $hasLongWord = true;
            }
        }

        return $hasLongWord;
    }

    private function looksLikeCompany(string $line): bool
    {
        $words = [
            'ltd', 'limited', 'pvt', 'llc', 'inc', 'gmbh', 'corp', 'company',
            'enterprises', 'industries', 'tiles', 'surfaces', 'homes', 'group',
            'tour', 'tours', 'travel', 'travels', 'services', 'exports', 'builders',
        ];

        foreach ($words as $word) {
            if (preg_match('/\b'.preg_quote($word, '/').'\b/i', $line)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeCity(string $line): bool
    {
        if (preg_match('/\d|@/', $line) || strlen($line) > 40) {
            return false;
        }

        $words = preg_split('/\s+/', $line);

        if (count($words) < 1 || count($words) > 3) {
            return false;
        }

        foreach ($words as $word) {
            if (! preg_match("/^[A-Za-z][A-Za-z.'-]*$/", $word)) {
                return false;
            }
        }

        return true;
    }

    private function consume(array &$lines, array &$used, $index, string $piece): void
    {
        $left = trim(str_ireplace($piece, '', $lines[$index]));
        $left = trim($left, " \t|:;,.-");

        if ($left === '' || preg_match('/^(ph|phone|tel|mobile|mob|email|e-mail|web|website)$/i', $left)) {
            $used[$index] = true;

            return;
        }

        $lines[$index] = $left;
    }

    private function cut(string $value, int $max): string
    {
        if (strlen($value) <= $max) {
            return $value;
        }

        return substr($value, 0, $max);
    }
}
