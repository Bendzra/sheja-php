/*
	Based on
		http://digitaltibetan.org/tibetan/JavaWylie-dev.zip
		https://github.com/buda-base/ewts-converter

	Simplified to convert only from Unicode to Wylie:
		Processing unicode else keeping as it is.
*/

//    *** constant hashes and sets to help with the conversion *** //

// helper function: convert an array to an object with each array element => true
const array2set = arr => Object.fromEntries(arr.map(x => [ x, true ]));

//    *** Unicode to Wylie mappings *** //

const UW = {
	m_tib_top       : null,  // HashMap<Character, String>
	m_tib_subjoined : null,  // HashMap<Character, String>
	m_tib_vowel     : null,  // HashMap<Character, String>
	m_tib_vowel_long: null,  // HashMap<String, String>
	m_tib_final     : null,  // HashMap<Character, ArrayList<String>>
	m_tib_caret     : null,  // HashMap<String, String>
	m_tib_other     : null,  // HashMap<Character, String>
	
	tib_top         : function(s) { return this.m_tib_top[s];        },
	tib_subjoined   : function(s) { return this.m_tib_subjoined[s];  },
	tib_vowel       : function(s) { return this.m_tib_vowel[s];      },
	tib_vowel_long  : function(s) { return this.m_tib_vowel_long[s]; },
	tib_final       : function(s) { return this.m_tib_final[s];      },
	tib_caret       : function(s) { return this.m_tib_caret[s];      },
	tib_other       : function(s) { return this.m_tib_other[s];      },

	initialized     : false // Static initialization flag
};

//         *** Wylie to Wylie Sets mappings for conditions ***
const WW = {
	m_tib_stacks: null, // HashSet<String>
	m_prefixes  : null, // HashMap<String, HashSet<String>>
	m_suffixes  : null, // HashSet<String>
	m_suff2     : null, // HashMap<String, HashSet<String>>
	m_ambiguous : null,
	
	tib_stack   : function(s) { return this.m_tib_stacks[s]; },
	is_prefix   : function(s) { return !!this.m_prefixes[s]; },
	is_suffix   : function(s) { return this.m_suffixes[s]; },
	is_suff2    : function(s) { return !!this.m_suff2[s]; },
	prefix      : function(pref, after)  { let s = this.m_prefixes[pref]; return s && s[after]; },
	suff2       : function(suff, before) { let s = this.m_suff2[suff]; return s && s[before]; },
	ambiguous   : function(s) { return this.m_ambiguous[s]; },
			    
	initialized : false // Static initialization flag
};


// Static method to initialize all the hashes with
// the correspondences between Unicode and Wylie.
UW.init = function() {

	// Initialize static hashes if not already done
	if (this.initialized) return;
	this.initialized = true;
	
	// top letters
	this.m_tib_top = {
		"\u0f40": "k" , "\u0f41": "kh" , "\u0f42": "g" , "\u0f43": "g+h" , "\u0f44": "ng",
		"\u0f45": "c" , "\u0f46": "ch" , "\u0f47": "j" ,                   "\u0f49": "ny",
		"\u0f4a": "T" , "\u0f4b": "Th" , "\u0f4c": "D" , "\u0f4d": "D+h" , "\u0f4e": "N",
		"\u0f4f": "t" , "\u0f50": "th" , "\u0f51": "d" , "\u0f52": "d+h" , "\u0f53": "n",
		"\u0f54": "p" , "\u0f55": "ph" , "\u0f56": "b" , "\u0f57": "b+h" , "\u0f58": "m",
		"\u0f59": "ts", "\u0f5a": "tsh", "\u0f5b": "dz", "\u0f5c": "dz+h", "\u0f5d": "w",
		"\u0f5e": "zh", "\u0f5f": "z"  , "\u0f60": "'" , "\u0f61": "y"   ,
		"\u0f62": "r" , "\u0f63": "l"  , "\u0f64": "sh", "\u0f65": "Sh"  , "\u0f66": "s",
		"\u0f67": "h" , "\u0f68": "a"  ,
		"\u0f69": "k+Sh",
		"\u0f6a": "R"
	};
	
	// subjoined letters
	this.m_tib_subjoined = {
		"\u0f90": "k" , "\u0f91": "kh" , "\u0f92": "g" , "\u0f93": "g+h" , "\u0f94": "ng",
		"\u0f95": "c" , "\u0f96": "ch" , "\u0f97": "j" ,                   "\u0f99": "ny",
		"\u0f9a": "T" , "\u0f9b": "Th" , "\u0f9c": "D" , "\u0f9d": "D+h" , "\u0f9e": "N",
		"\u0f9f": "t" , "\u0fa0": "th" , "\u0fa1": "d" , "\u0fa2": "d+h" , "\u0fa3": "n",
		"\u0fa4": "p" , "\u0fa5": "ph" , "\u0fa6": "b" , "\u0fa7": "b+h" , "\u0fa8": "m",
		"\u0fa9": "ts", "\u0faa": "tsh", "\u0fab": "dz", "\u0fac": "dz+h", "\u0fad": "w",
		"\u0fae": "zh", "\u0faf": "z"  , "\u0fb0": "'" , "\u0fb1": "y"   ,
		"\u0fb2": "r" , "\u0fb3": "l"  , "\u0fb4": "sh", "\u0fb5": "Sh"  , "\u0fb6": "s",
		"\u0fb7": "h" , "\u0fb8": "a"  ,
		"\u0fb9": "k+Sh",
		"\u0fba": "W",
		"\u0fbb": "Y",
		"\u0fbc": "R"
	};
	
	// vowel signs:
	// a-chen is not here because that's a top character, not a vowel sign.
	// pre-composed "I" and "U" are dealt here; other pre-composed Skt vowels are more
	// easily handled by a global replace in toWylie(), b/c they turn into subjoined "r"/"l".

	this.m_tib_vowel = {
		"\u0f71": "A",
		"\u0f72": "i",
		"\u0f73": "I",
		"\u0f74": "u",
		"\u0f75": "U",
		"\u0f7a": "e",
		"\u0f7b": "ai",
		"\u0f7c": "o",
		"\u0f7d": "au",
		"\u0f80": "-i"
	};

	// long (Skt) vowels
	this.m_tib_vowel_long = {
		"i": "I",
		"u": "U",
		"-i": "-I"
	};

	// final symbols: unicode => [ wylie, class ] (cannot have more than 1 of the same class in the same stack)
	this.m_tib_final = {
		"\u0f35": [ "~X",  "X" ],
		"\u0f37": [ "X",   "X" ],
		"\u0f39": [ "^",   "^" ],
		"\u0f7e": [ "M",   "M" ],
		"\u0f7f": [ "H",   "H" ],
		"\u0f82": [ "~M`", "M" ],
		"\u0f83": [ "~M",  "M" ],
		"\u0f84": [ "?",   "?" ],
		"\u0f85": [ "&",   "&" ],
	};

	
	// special characters introduced by ^
	this.m_tib_caret = {
		"ph": "f",
		"b" : "v",
	};

	// other stand-alone characters
	this.m_tib_other = {
		" ": "_",
		"\u0f04": "@",
		"\u0f05": "#",
		"\u0f06": "$",
		"\u0f07": "%",
		"\u0f08": "!",
		"\u0f0b": " ", // Intersyllabic Tsheg
		"\u0f0c": "*", // Delimiter Tsheg Bstar
		"\u0f0d": "/",
		"\u0f0e": "//",
		"\u0f0f": ";",
		"\u0f11": "|",
		"\u0f14": ":",
		"\u0f20": "0",
		"\u0f21": "1",
		"\u0f22": "2",
		"\u0f23": "3",
		"\u0f24": "4",
		"\u0f25": "5",
		"\u0f26": "6",
		"\u0f27": "7",
		"\u0f28": "8",
		"\u0f29": "9",
		"\u0f34": "=",
		"\u0f3a": "<",
		"\u0f3b": ">",
		"\u0f3c": "(",
		"\u0f3d": ")"
	};

};

///////////////////////////////////////////////////////////////////

WW.init = function() {

	// Initialize static hashes if not already done
	if (this.initialized) return;
	this.initialized = true;

	// all these stacked consonant combinations don't need "+"s in them
	this.m_tib_stacks = array2set([
		"b+l","b+r","b+y",
		"c+w",
		"d+r","d+r+w","d+w",
		"g+l","g+r","g+r+w","g+w","g+y",
		"h+r","h+w",
		"k+l","k+r","k+w","k+y",
		"kh+r","kh+w","kh+y",
		"l+b","l+c","l+d","l+g","l+h","l+j","l+k","l+ng","l+p","l+t","l+w",
		"m+r","m+y",
		"n+r","ny+w",
		"p+r","p+y",
		"ph+r","ph+y","ph+y+w",
		"r+b","r+d","r+dz","r+g","r+g+w","r+g+y","r+j","r+k","r+k+y","r+l","r+m","r+m+y","r+n","r+ng","r+ny","r+t","r+ts","r+ts+w","r+w",
		"s+b","s+b+r","s+b+y",
		"s+d",
		"s+g","s+g+r","s+g+y",
		"s+k","s+k+r","s+k+y",
		"s+l",
		"s+m","s+m+r","s+m+y",
		"s+n","s+n+r",
		"s+ng",
		"s+ny",
		"s+p","s+p+r","s+p+y",
		"s+r",
		"s+t",
		"s+ts","s+w",
		"sh+r","sh+w",
		"t+r","t+w",
		"th+r",
		"ts+w",
		"tsh+w",
		"z+l","z+w",
		"zh+w"
	]);

	// prefixes => set of consonants or stacks after
	this.m_prefixes = {
		"g": array2set(["c","ny","t","d","n","ts","zh","z","y","sh","s"]),
		"d": array2set(["k","g","ng","p","b","m","k+y","g+y","p+y","b+y","m+y","k+r","g+r","p+r","b+r"]),
		"b": array2set([
				"k","g","c","t","d","ts","zh","z","sh","s","r","l",
				"k+y","g+y",
				"k+r","g+r",
				"r+l","s+l",
				"r+k","r+g","r+ng","r+j","r+ny","r+t","r+d","r+n","r+ts","r+dz",
				"s+k","s+g","s+ng","s+ny","s+t","s+d","s+n","s+ts",
				"r+k+y","r+g+y","s+k+y","s+g+y",
				"s+k+r","s+g+r",
				"l+d","l+t",
				"k+l","s+r","z+l","s+w"
			]),
		"m": array2set(["kh","g","ng","ch","j","ny","th","d","n","tsh","dz","kh+y","g+y","kh+r","g+r"]),
		"'": array2set(["kh","g","ch","j","th","d","ph","b","tsh","dz","kh+y","g+y","ph+y","b+y","kh+r","g+r","d+r","ph+r","b+r"]),
	};

	// set of suffix letters
	// also included are some Skt letters b/c they occur often in suffix position in Skt words
	this.m_suffixes = array2set(["'","g","ng","d","n","b","m","r","l","s","N","T","-n","-t"]);
	
	// suffix2 => set of letters before
	this.m_suff2 = {
		"s": array2set(["g","ng","b","m"]),
		"d": array2set(["n","r","l"]),
	};

	// root letter index for very ambiguous 3 letter syllables: consonant string => [ root index, "wylie result" ]
	this.m_ambiguous = {
		"dgs": [ 1, "dgas" ],
		"dngs": [ 1, "dngas" ],
		"'gs": [ 1, "'gas" ],
		"'bs": [ 1, "'bas" ],
		"dbs": [ 1, "dbas" ],
		"dms": [ 1, "dmas" ],
		"bgs": [ 0, "bags" ],
		"mngs": [ 0, "mangs" ],
		"mgs": [ 0, "mags" ],

		// some syllables ending in '-d' added here to silence some warnings
		"gnd": [ 1, "gnad" ],
	};
};


///////////////////////////////////////////////////////////////////

// Output

const OUT = {
	warnings: [],
	warn    : function(str) { this.warnings.push(str); },
	get_warnings: function() { return this.warnings; }
};

///////////////////////////////////////////////////////////////////
/*        Joining UW with WW for private analysis                */

const HAND = {};

// Puts an analyzed stack together into EWTS output, adding an implicit "a" if needed.
HAND._put_stack_together = function(stack) {
	
	let out = "";

	// put the main elements together... stacked with "+" unless it's a regular stack
	if (WW.tib_stack(stack.cons_str)) {
		out += stack.stack.join("")
	} else {
		out += stack.cons_str;
	}

	// caret (tsa-phru) goes here as per some (halfway broken) Unicode specs...
	if (stack.caret) out += '^';

	// vowels...
	if (stack.vowels.length > 0) {
		out += stack.vowels.join("+");

	} else if (!stack.prefix && !stack.suffix && !stack.suff2 && !stack.cons_str.match(/a$/)) {
		out += 'a';
	}

	// final stuff
	out += stack.finals.join("");
	if (stack.dot) out += '.';

	return out;
};

// Unicode to EWTS: one stack at a time
HAND._to_ewts_one_stack = function(str, len, i) {

	let orig_i = i, warns = [];
	let ffinal = null, vowel = null, klass = null;

	// analyze the stack into: 
	//   - top symbol
	//   - stacked signs (first is the top symbol again, then subscribed main characters...)
	//   - caret (did we find a stray tsa-phru or not?)
	//   - vowel signs (including small subscribed a-chung, "-i" Skt signs, etc)
	//   - final stuff (including anusvara, visarga, halanta...)
	//   - and some more variables to keep track of what has been found
	let stack = {
		top:          null, // top symbol
		stack:          [], // [ consonants and also a-chen ]
		caret:       false, // caret found?
		vowels:         [], // [ vowels found ]
		finals:         [], // [ finals found ]

		finals_found:   {}, // { klass of final => ewts }
		visarga:     false, // visarga found
		cons_str:     null, // all stack elements separated by "+" (including 'a-chen')
		single_cons:  null, // is this a single consonant with no vowel signs or finals?
		prefix:      false, // later set to true if it's a prefix
		suffix:      false, // later set to true if it's a suffix
		suff2:       false, // later set to true if it's a second suffix
		dot:         false, // later set to true if we need a '.' after this stack (ex. "g.yag")
	};

	// for easy access
	let stst = stack.stack, stvow = stack.vowels;

	// assume: UW.tib_top(t) exists
	let t = str.charAt(i++);
	stack.top = UW.tib_top(t);
	stst.push(UW.tib_top(t));

	// grab everything else below the top sign and classify in various categories
	while (i < len) {
		t = str.charAt(i);
		let o;
		
		if ((o = UW.tib_subjoined(t))) {
			i++;
			stst.push(o);

			// check for bad ordering
			if (stack.finals.length > 0) {
				warns.push(`Subjoined sign "${o}" found after final sign "${ffinal}".`);
			} else if (stvow.length > 0) {
				warns.push(`Subjoined sign "${o}" found after vowel sign "${vowel}".`);
			}

		} else if ((o = UW.tib_vowel(t))) {
			i++;
			stvow.push(o);
			if (vowel === null) vowel = o;

			// check for bad ordering
			if (stack.finals.length > 0) {
				warns.push(`Vowel sign "${o}" found after final sign "${ffinal}".`);
			}

		} else if ((o = UW.tib_final(t))) {
			i++;
			[ o, klass ] = o;

			if (o === '^') {
				stack.caret = true;
			} else {
				if (o === 'H') stack.visarga = true;
				if (ffinal === null) ffinal = o;

				// check for invalid combinations
				if (stack.finals_found[klass] !== undefined) {
					warns.push(`Final sign "${o}" should not combine with final sign "${stack.finals_found[klass]}".`);
				} else {
					stack.finals_found[klass] = o;
					stack.finals.push(o);
				}
			}

		} else {
			break;
		}
	}

	// now analyze the stack according to various rules:

	// a-chen with vowel signs: remove the "a" and keep the vowel signs
	if (stack.top === "a" && stst.length === 1 && stvow.length > 0) {
		stst.shift();
	}

	// handle long vowels: A+i becomes I, etc.
	if (stvow.length > 1 && stvow[0] === 'A' && UW.tib_vowel_long(stvow[1])) {
		stvow.splice(0, 2, UW.tib_vowel_long(stvow[1]));
	}

	// Sanskrit vocalic 'r' and 'l' (r-i, r-I, l-i, l-I): treat them as vowels
	if (stst.length > 0
		&& stst[stst.length - 1] in ['r', 'l']
		&& stvow.length === 1
		&& stvow[0] in ['-i', '-I'])
	{
		let rl = stst.pop();
		stvow[0] = rl + stvow[0];
	}

	// special cases: "ph^" becomes "f", "b^" becomes "v"
	if (stack.caret && stst.length === 1 && UW.tib_caret(stack.top)) {
		stack.top = stst[0] = UW.tib_caret(stack.top);
		stack.caret = false;

	}

	stack.cons_str = stst.join("+");

	// if this is a single consonant, keep track of it (useful for prefix/suffix analysis)
	if (stst.length === 1
		&& stst[0] !== 'a'
		&& !stack.caret
		&& stvow.length === 0
		&& stack.finals.length === 0)
	{
		stack.single_cons = stack.cons_str;
	}

	// return the analyzed stack
	return {
		toks: i - orig_i,	// tokens used
		stack,
		warns
	};
};

// Convert Unicode to EWTS: one tsekbar
HAND._to_ewts_one_tsekbar = function(str, len, i) {
	
	let orig_i = i, warns = [], stacks = [];
	
	// make a list of stacks, until we get to punctuation or to a visarga
	while (true)
	{
		let { toks, stack, warns: these_warns } = this._to_ewts_one_stack(str, len, i);
		stacks.push(stack);
		warns = warns.concat(these_warns);
		i += toks;

		if (stack.visarga) break;
		if (i >= len || !UW.tib_top(str.charAt(i))) break;
	}

	// figure out if some of these stacks can be prefixes or suffixes (in which case
	// they don't need their "a" vowels)
	let num_stacks = stacks.length, last = num_stacks - 1;
	if (num_stacks > 1 && stacks[0].single_cons) {
		// we don't count the wazur in the root stack, for prefix checking
		let cs = stacks[1].cons_str.replaceAll("+w", "");
		if (WW.prefix(stacks[0].single_cons, cs)) {
			stacks[0].prefix = true;
		}
	}

	if (num_stacks > 1 && stacks[last].single_cons && WW.is_suffix(stacks[last].single_cons)) {
		stacks[last].suffix = true;
	}

	if (num_stacks > 2
		&& stacks[last].single_cons
		&& stacks[last - 1].single_cons
		&& WW.is_suffix(stacks[last - 1].single_cons)
		&& WW.is_suff2(stacks[last].single_cons)
		&& WW.suff2(stacks[last].single_cons, stacks[last - 1].single_cons))
	{
		stacks[last].suff2 = true;
		stacks[last - 1].suffix = true;
	}

	// if there are two stacks and both can be prefix-suffix, then 1st is root
	if (num_stacks === 2 && stacks[0].prefix && stacks[1].suffix) {
		stacks[0].prefix = false;
	}

	// if there are three stacks and they can be prefix, suffix and suff2, then check w/ a table
	if (num_stacks === 3 && stacks[0].prefix && stacks[1].suffix && stacks[2].suff2) {
		let str = stacks.map(x => x.single_cons).join("");
		let amb = WW.ambiguous(str);
		let root;

		if (amb) {
			root = amb[0];
		} else {
			warns.push(`Ambiguous syllable found: root consonant not known for "${str}".`);
			// make it up... 
			root = 1;
		}

		stacks[root].prefix = stacks[root].suffix = false;
		stacks[root + 1].suff2 = false;
	}

	// if the prefix together with the main stack could be mistaken for a single stack, add a "."
	if (stacks[0].prefix && WW.tib_stack(stacks[0].single_cons + "+" + stacks[1].cons_str)) {
		stacks[0].dot = true;
	}

	// if some stack after the first starts with a vowel, put a dot before it (ex. "ba.a")
	for (let k = 1; k < stacks.length; k++) {
		if (stacks[k].top == 'a') stacks[k - 1].dot = true;
	}

	// put it all together
	let wylie = stacks.map(x => this._put_stack_together(x)).join("");

	return {
		toks: i - orig_i,	// number of tokens used
		wylie,			// ewts produced
		warns,			// warnings produced
		stacks			// analyzed stacks
	};
};

HAND._initHashes = function() {
	UW.init();
	WW.init();
};

///////////////////////////////////////////////////////////////////
// Dummy для тестов

function to_unicode(str)
{
	return "Not to be implemented here";
}

///////////////////////////////////////////////////////////////////

// Converts a string from Unicode to EWTS
// Returns: the transliterated string
// To get the warnings, call ewts.get_warnings() afterwards.
//
// все нетибетские символы оставляем как есть
//
function to_ewts(str)
{
	HAND._initHashes();
	
	let out = "", line = 1, units = 0;
	OUT.warnings = [];

	// globally search and replace some deprecated pre-composed Sanskrit vowels, and a pre-composed oM
	str = str.replaceAll("\u0f76", "\u0fb2\u0f80")
		.replaceAll("\u0f77", "\u0fb2\u0f71\u0f80")
		.replaceAll("\u0f78", "\u0fb3\u0f80")
		.replaceAll("\u0f79", "\u0fb3\u0f71\u0f80")
		.replaceAll("\u0f81", "\u0f71\u0f80")
		.replaceAll("\u0f75", "\u0f71\u0f74")
		.replaceAll("\u0f73", "\u0f71\u0f72")
		.replaceAll("\u0f00", "\u0f68\u0f7c\u0f7e");
	
	let i = 0, len = str.length;

	// iterate over the string, codepoint by codepoint
	while (i < len)
	{
		let t = str.charAt(i);

		// found tibetan script - handle one tsekbar
		if (UW.tib_top(t))
		{
			// we don't need the analyzed stacks in 'stacks'
			let { toks, wylie, warns } = HAND._to_ewts_one_tsekbar(str, len, i);
			out += wylie; i += toks; units++;

			for (let w of warns) OUT.warn(`line ${line}: ${w}`);
			continue;
		}

		// don't do spaces if there is non-tibetan coming, so they become part of the [  escaped block].
		let o = UW.tib_other(t);
		if ( o && t !== ' ' )
		{
			out += o; i++; units++;
			continue;
		}

		// newlines, count lines. "\r\n" together count as one newline.
		if (t === "\n" || t === "\r")
		{
			line++; i++; out += t;

			if (t === "\r" && i < len && str.charAt(i) === "\n") { i++; out += "\n"; }
			continue;
		}

		// ignore BOM and zero-width space
		if (t === "\ufeff" || t === "\u200b")
		{
			i++;
			continue;
		}

		// anything else - pass along?
		out += t;
		i++;
	}

	if (units === 0) OUT.warn(`No Tibetan characters found!`);
	return out;
}
