<?php
/*
Maintatining a FILO dictionary of known-good matches, this class will predict
what the user meant to search for.

Licensed under GPLv3, Copyright Callan Bryant <callan.bryant@gmail.com>
*/

class didYouMean
{
	protected $store;
	protected $dict = array(null);
	// max phrases to have in the dictionary
	public static $maxPhrases = 500;
	// flag to skip saving dict
	protected $updated = false;

	public function __construct($namespace = null)
	{
		$this->store = new keyStore($namespace.'didyoumean!!');
		$this->dict = $this->store->get('dictionary');

		if (!$this->dict)
			$this->dict = array(null);
	}

	// returnsnull on no good guess
	public function add($phrase)
	{
		if (!is_string($phrase))
			throw new Exception('phrase must be string');

		// no case sensitivity!
		$phrase = strtolower($phrase);

		if (in_array($phrase,$this->dict))
			return;

		$this->dict[] = $phrase;
		$this->updated = true;
	}

	public function guess($phrase)
	{
		// no case sensitivity!
		$phrase = strtolower($phrase);

		$scores = array();

		foreach ($this->dict as &$possibility)
			$scores[$possibility] = levenshtein($phrase,$possibility);
	
		$lowest	= 10000;
	
		foreach($scores as $possibility => $score)
			if($score < $lowest)
			{
				$lowest = $score;
				$guess = $possibility;
			}
	
		return $guess;
	}

	public function flush()
	{
		$this->store->flush();
	}

	public function __destruct()
	{
		if (!$this->updated)
			return;

		// trim the dict (lazy approach, I can get away with it because,
		// normally this will loop once)
		while (count($this->dict) > self::$maxPhrases)
			array_shift($this->dict);

		// save the dict
		$this->store->set('dictionary',$this->dict);
	}
}
