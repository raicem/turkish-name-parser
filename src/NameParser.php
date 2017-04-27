<?php
/**
 * @package TurkishNameParser
 * @licence MIT
 * @author  Cem Ünalan <unalancem@gmail.com>
 */

namespace Raicem;

class NameParser
{
    /** @var string */
    protected $rawName;

    /** @var array */
    protected $rawNameArray;

    /** @var array */
    protected $result;

    /** @var array */
    protected $invalidChunks;

    /** @var boolean */
    protected $validity;

    /**
     * Parses the name.
     *
     * @param string $name
     * @return NameParser $this
     */
    public function parse($name)
    {
        $this->rawName = $name;
        $this->rawNameArray = explode(' ', trim($this->rawName));
        $this->createResult();
        return $this;
    }

    /**
     * Getter for the raw name given by the user.
     *
     * @return string
     */
    public function getRawName()
    {
        return $this->rawName;
    }

    /**
     * Getter for the raw name as array as it has been created in the parse method.
     *
     * @return array
     */
    public function getRawArray()
    {
        return $this->rawNameArray;
    }

    /**
     * Returns the result as array.
     *
     * @return array
     */
    public function asArray()
    {
        return $this->result;
    }

    /**
     * Returns result as string.
     *
     * @return string
     */
    public function asString()
    {
        return implode(' ', $this->result);
    }

    /**
     * Parses and cleans up the name.
     */
    public function createResult()
    {
        $this->cleanCells()
            ->removeInvalidNames()
            ->capitalizeFirstLetters()
            ->ordersNames()
            ->removeRepeatedNames();
    }

    /**
     * Cleans the each chunk/cell of the raw name and saves it to the $results variable.
     *
     * @return NameParser $this
     */
    public function cleanCells()
    {
        $this->result = array_map(function ($cell) {
            $cell = strip_tags($cell);
            $cell = $this->convertToLowercase($cell);
            $cell = $this->removeCharsAndNumbers($cell);
            $cell = trim($cell);
            $cell = $this->removeRepeatingStartingLetters($cell);
            return $cell;
        }, $this->getRawArray());
        return $this;
    }

    /**
     * Converts strings to lowercase. Turkish language has a dotted and dotless I
     * ({@link https://en.wikipedia.org/wiki/Dotted_and_dotless_I}) and
     * mb_strtolower function can not convert it to lower case.
     * Manual replacement of the characters are neccessery.
     *
     * @param $string
     * @return string
     */
    public function convertToLowercase($string)
    {
        // manually replacing I and İ letters.
        $string = str_ireplace('I', 'ı', $string);
        $string = str_ireplace('İ', 'i', $string);

        return mb_strtolower($string, 'UTF-8');
    }

    /**
     * Removes everything that is not a letter. PL is a unicode shortcut for selecting letters.
     *
     * @param $string
     * @return mixed
     */
    public function removeCharsAndNumbers($string)
    {
        return preg_replace('/\PL/u', '', $string);
    }

    /**
     * In Turkish there are no names starting with the same letter repeated such as Aaron etc.
     * This method removes these types of repeating letters from the string.
     *
     * @param $string
     * @return string
     */
    public function removeRepeatingStartingLetters($string)
    {
        $letters = mb_str_split($string, 1);
        if (count($letters) > 1 && ($letters[0] === $letters[1])) {
            unset($letters[0]);
            $string = implode('', $letters);
        }
        return $string;
    }

    /**
     * Filters invalid names from the results array.
     *
     * @return $this
     */
    public function removeInvalidNames()
    {
        $this->result = array_filter($this->result, function ($cell) {
            $result = $this->checkIfNameIsInvalid($cell);
            if ($result === false) {
                $this->addInvalidChunk($cell);
            }
            return $result;
        });

        return $this;
    }

    /**
     * Number of letters are equal to one or less than one this is considered invalid name.
     * Number of vowels are equal to one or less than one this is considered invalid name.
     * If number of vowels and letters are equal to each other
     * this in considered invalid name.
     *
     * @param $string
     * @return bool
     */
    public function checkIfNameIsInvalid($string)
    {
        $length = mb_strlen($string);
        $vowels = $this->numberOfVowels($string);
        return $length >= 1 && $vowels >= 1 && $length !== $vowels;
    }

    /**
     * Counts the number of vowels in a string.
     *
     * @param $string
     * @return int
     */
    public function numberOfVowels($string)
    {
        $vowels = [
            'a',
            'e',
            'ı',
            'i',
            'o',
            'ö',
            'u',
            'ü',
            'A',
            'E',
            'I',
            'İ',
            'O',
            'Ö',
            'U',
            'ü'
        ];

        $characters = mb_str_split($string, 1);
        $count = 0;
        foreach ($characters as $char) {
            if (in_array($char, $vowels, true)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Capitalizes the first letters of names by mapping the results array.
     *
     * @return $this
     */
    public function capitalizeFirstLetters()
    {
        $this->result = array_map(function ($cell) {
            return $this->capitalizeFirstLetter($cell);
        }, $this->result);
        return $this;
    }

    /**
     * Capitalizes first letters of a string. Turkish language has a dotted and dotless I
     * ({@link https://en.wikipedia.org/wiki/Dotted_and_dotless_I}) and
     * mb_convert_case function can not convert it to lower case.
     * Manual replacement of the characters are neccessery.
     *
     * @param $string
     * @return string
     */
    public function capitalizeFirstLetter($string)
    {
        if (mb_strpos($string, 'i') === 0) {
            $string = 'İ' . ltrim($string, 'i');
        } elseif (mb_strpos($string, 'ı') === 0) {
            $string = 'I' . ltrim($string, 'ı');
        }
        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Orders names within an array.
     *
     * @return $this|null
     */
    protected function ordersNames()
    {
        $this->result = array_values($this->result);
        $count = count($this->result);

        if ($count <= 1) {
            $this->validity = false;
            $this->result = null;
            return $this;
        }

        $data['first_name'] = $this->result[0];
        switch ($count) {
            case ($count === 2):
                $data['last_name'] = $this->result[1];
                break;
            case ($count === 3):
                $data['middle_name'] = $this->result[1];
                $data['last_name'] = $this->result[2];
                break;
            case ($count > 3):
                $data['middle_name'] = $this->result[1];
                $restOfTheName = array_splice($this->result, 2);
                $data['last_name'] = implode(' ', $restOfTheName);
                break;
        }
        $this->validity = true;
        $this->result = $data;
        return $this;
    }

    /**
     * Removes middle name if it is same as the first name.
     * @return $this
     */
    protected function removeRepeatedNames()
    {
        if (isset($this->result['middle_name']) && $this->result['first_name'] === $this->result['middle_name']) {
            unset($this->result['middle_name']);
        }

        return $this;
    }

    /**
     * Adds invalid name to invalid names array.
     *
     * @param $string
     */
    public function addInvalidChunk($string)
    {
        $this->invalidChunks[] = $string;
    }

    /**
     * Gets invalid names array.
     *
     * @return array
     */
    public function getInvalidChunks()
    {
        return $this->invalidChunks;
    }

    public function isValid()
    {
        return $this->validity;
    }

    public function __toString()
    {
        return $this->asString();
    }
}
