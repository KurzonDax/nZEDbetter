<?php
require_once(WWW_DIR."/lib/groups.php");
require_once(WWW_DIR."/lib/predb.php");
require_once(WWW_DIR."/lib/framework/db.php");


//
//	Cleans names for collections/releases/imports/namefixer.
//
class nameCleaning
{
	//
	//	Cleans usenet subject before inserting, used for collectionhash. Uses groups first (useful for bunched collections).
	//
	public function collectionsCleaner($subject, $type="normal", $groupID="")
	{
        if(!isset($db))
            $db = new DB();
        $regexStringsResult = $db->queryDirect("SELECT * FROM searchnameRegex WHERE UseForCollections=1 ORDER BY ID ASC");
        if($regexStringsResult)
        {
            while($regexStringRow = $db->fetchAssoc($regexStringsResult))
            {
                $regexString = "/".$regexStringRow['regexString']."/";
                if($regexStringRow['caseSensitive']=="0")
                    $regexString = $regexString."i";

                $backRef = $regexStringRow['backReferenceNum'];

                if(preg_match($regexString, $subject, $matches))
                {
                    if(count($matches)>=$backRef+1)
                    {
                        $cleanerName = $this->collectionsCleanerHelper($matches[$backRef], $type);
                        return $cleanerName;
                    }
                }
            }
            // If we made it here, no regex matched
            return $this->collectionsCleanerHelper($subject, $type);

        }
        else return $this->collectionsCleanerHelper($subject, $type);
	}

	//
	//	Cleans usenet subject before inserting, used for collectionhash. Fallback from collectionsCleaner.
	//
	public function collectionsCleanerHelper($subject, $type)
	{
		/* This section is more generic, it will work on most releases. */
		//Parts/files
		$cleansubject = preg_replace('/((( \(\d\d\) -|(\d\d)? - \d\d\.|\d{4} \d\d -) | - \d\d-| \d\d\. [a-z]).+| \d\d of \d\d| \dof\d)\.mp3"?|(\(|\[|\s)\d{1,4}(\/|(\s|_)of(\s|_)|\-)\d{1,4}(\)|\]|\s|$|:)|\(\d{1,3}\|\d{1,3}\)|\-\d{1,3}\-\d{1,3}\.|\s\d{1,3}\sof\s\d{1,3}\.|\s\d{1,3}\/\d{1,3}|\d{1,3}of\d{1,3}\.|^\d{1,3}\/\d{1,3}\s|\d{1,3} - of \d{1,3}/i', ' ', $subject);
		//Anything between the quotes. Too much variance within the quotes, so remove it completely.
		$cleansubject = preg_replace('/\".+\"/i', ' ', $cleansubject);
		//File extensions - If it was not quotes.
		$cleansubject = preg_replace('/(-? [a-z0-9]+-?|\(?\d{4}\)?(_|-)[a-z0-9]+)\.jpg"?| [a-z0-9]+\.mu3"?|((\d{1,3})?\.part(\d{1,5})?|\d{1,5} ?|sample|- Partie \d+)?\.(7z|\d{3}(?=(\s|"))|avi|diz|docx?|epub|idx|iso|jpg|m3u|m4a|mds|mkv|mobi|mp4|nfo|nzb|par(\s?2|")|pdf|rar|rev|rtf|r\d\d|sfv|srs|srr|sub|txt|vol.+(par2)|xls|zip|z{2,3})"?|(\s|(\d{2,3})?\-)\d{2,3}\.mp3|\d{2,3}\.pdf|\.part\d{1,4}\./i', ' ', $cleansubject);
		//File Sizes - Non unique ones.
		$cleansubject = preg_replace('/\d{1,3}(,|\.|\/)\d{1,3}\s(k|m|g)b|(\])?\s\d{1,}KB\s(yENC)?|"?\s\d{1,}\sbytes?|(\-\s)?\d{1,}(\.|,)?\d{1,}\s(g|k|m)?B\s\-?(\s?yenc)?|\s\(d{1,3},\d{1,3}\s{K,M,G}B\)\s|yEnc \d+k$|{\d+ yEnc bytes}|yEnc \d+ |\(\d+ ?(k|m|g)?b(ytes)?\) yEnc/i', ' ', $cleansubject);
		//Random stuff.
		$cleansubject = preg_replace('/AutoRarPar\d{1,5}|\(\d+\)( |  )yEnc|\d+(Amateur|Classic)| \d{4,}[a-z]{4,} |part\d+/i', ' ', $cleansubject);
		$cleansubject = utf8_encode(trim(preg_replace('/\s\s+/i', ' ', $cleansubject)));

		if ($type == "split")
		{
			$one = $two = "";
			if (preg_match('/"(.+?)\.[a-z0-9].+?"/i', $subject, $matches))
				$one = $matches[1];
			else if(preg_match('/s\d{1,3}[.-_ ]?(e|d)\d{1,3}|EP[\.\-_ ]?\d{1,3}[\.\-_ ]|[a-z0-9\.\-_ \(\[\)\]{}<>,"\'\$^\&\*\!](19|20)\d\d[a-z0-9\.\-_ \(\[\)\]{}<>,"\'\$^\&\*\!]/i', $subject, $matches2))
				$two = $matches2[0];
			return $cleansubject.$one.$two;
		}
		else if ($type !== "split" && (strlen($cleansubject) <= 7 || preg_match('/^[a-z0-9 \-\$]{1,9}$/i', $cleansubject)))
		{
			$one = $two = "";
			if (preg_match('/.+?"(.+?)".+?".+?".+/', $subject, $matches))
				$one = $matches[1];
			else if (preg_match('/(^|.+)"(.+?)(\d{2,3} ?\(\d{4}\).+?)?\.[a-z0-9].+?"/i', $subject, $matches))
				$one = $matches[2];
			if(preg_match('/s\d{1,3}[.-_ ]?(e|d)\d{1,3}|EP[\.\-_ ]?\d{1,3}[\.\-_ ]|[a-z0-9\.\-_ \(\[\)\]{}<>,"\'\$^\&\*\!](19|20)\d\d[a-z0-9\.\-_ \(\[\)\]{}<>,"\'\$^\&\*\!]/i', $subject, $matches2))
				$two = $matches2[0];
			if ($one == "" && $two == "")
			{
				$newname = preg_replace('/[a-z0-9]/i', '', $subject);
				if (preg_match('/[\!@#\$%\^&\*\(\)\-={}\[\]\|\\:;\'<>\,\?\/_ ]{1,3}/', $newname, $matches3))
					return $cleansubject.$matches3[0];
			}
			else
				return $cleansubject.$one.$two;
		}
		else
			return $cleansubject;
	}

	//
	//	Cleans a usenet subject before inserting, used for searchname. Also used for imports.
	//	Some of these also have MD5 Hashes, I will comment where they do.
	//
	public function releaseCleaner($subject, $groupID="")
	{
		if(!isset($db))
            $db = new DB();
        $regexStringsResult = $db->queryDirect("SELECT * FROM searchnameRegex ORDER BY ID ASC");
        $subject = str_replace('/', ' ', $subject);
        if($regexStringsResult)
        {
            while($regexStringRow = $db->fetchAssoc($regexStringsResult))
            {
                $regexString = "/".$regexStringRow['regexString']."/";
                if($regexStringRow['caseSensitive']=="0")
                    $regexString = $regexString."i";

                $backRef = $regexStringRow['backReferenceNum'];

                if(preg_match($regexString, $subject, $matches))
                {
                    if(count($matches)>=$backRef+1)
                    {
                        // echo "Match on regex ID ".$regexStringRow['ID']."\nPassthrough: ".$matches[$backRef]."\n";
                        $cleanerName = $this->releaseCleanerHelper($matches[$backRef]);
                        return $cleanerName;
                    }
                }
            }
            // If we made it here, no regex matched

            return $this->releaseCleanerHelper($subject);

        }
        else return $this->releaseCleanerHelper($subject);
	}
	
	public function releaseCleanerHelper($subject)
	{
		$debug = true;
        if($debug)
            echo "Start - ".$subject."\n";
		//File and part count.
		$cleanerName = preg_replace('/(File )?(\(|\[|\s)\d{1,4}(\/|(\s|_)of(\s|_)|\-)\d{1,4}(\)|\]|\s|$|:)|\(\d{1,3}\|\d{1,3}\)|\-\d{1,3}\-\d{1,3}\.|\s\d{1,3}\sof\s\d{1,3}\.|\s\d{1,3}\/\d{1,3}|\d{1,3}of\d{1,3}\.|^\d{1,3}\/\d{1,3}\s|\d{1,3} - of \d{1,3}/i', ' ', $subject);
		if($debug)
            echo "1 - ".$cleanerName."\n";
		//Size.
		$cleanerName = preg_replace('/\d{1,3}(\.|,)\d{1,3}\s(K|M|G)B|\d{1,}(K|M|G)B|\d{1,}\sbytes?|(\-\s)?\d{1,}(\.|,)?\d{1,}\s(g|k|m)?B\s\-(\syenc)?|\s\(d{1,3},\d{1,3}\s{K,M,G}B\)\s|\(\d+K\)\syEnc|yEnc \d+k$/i', ' ', $cleanerName);
        if($debug)
            echo "2 - ".$cleanerName."\n";
		//Extensions.
        //Modified the 'sub' match to require a period, otherwise it would truncate the word subject or submarine, etc
		$cleanerName = preg_replace('/ [a-z0-9]+\.jpg|((\d{1,3})?\.part(\d{1,5})?|\d{1,5}|sample)?(?<! No)\.(7z|\d{3}(?=(\s|"))|avi|epub|idx|iso|jpg|m4a|mds|mkv|mobi|mp4|nfo|nzb|pdf|rar|\.rev|rtf|r\d\d|sfv|srs|srr|\.sub|txt|vol.+(par2)|par(\s?2|")|zip|z{2})"?|(\s|(\d{2,3})?\-)\d{2,3}\.mp3|\d{2,3}\.pdf|yEnc|\.part\d{1,4}\./i', ' ', $cleanerName);
        if($debug)
            echo "3 - ".$cleanerName."\n";
		//Books + Music.
        // Had to remove the last two components of the below regex.  Not sure what they were there for to start with
        // and they were wreaking havoc on Movie names because it would match any year in the 2000's (2000-2099)
		$cleanerName = preg_replace('/((\d{1,2}-\d{1-2})?-[a-z0-9]+)?\.scr|Ebook\-[a-z0-9]+|\((\d+ )ebooks\)|\(ebooks[\.\-_ ](collection|\d+)\)|\([a-z]{3,9} \d{1,2},? 20\d\d\)|\(\d{1,2} [a-z]{3,9} 20\d\d|\[ATTN:.+?\]|ATTN: [a-z]{3,13} |ATTN:(macserv 100|Test)|ATTN: .+? - ("|:)|ATTN .+?:|\((bad conversion|Day\d{1,}\/\?|djvu|fixed|pdb|tif)\)|by [a-z0-9]{3,15}$|^Dutch(:| )|enjoy!|(\*| )enjoy(\*| )|^ePub |\(EPUB\+MOBI\)|(Flood )?Please - All I have|isbn\d+|New Ebooks \d{1,2} [a-z]{3,9} (19|20)\d\d( part \d)?|\[(MF|Ssc)\]|^New Version( - .+? - )?|^NEW( [a-z]+( Paranormal Romance|( [a-z]+)?:|,| ))?(?![\.\-_ ]York)|[\.\-_ ]NMR \d{2,3}|( |\[)NMR( |\])|\[op.+?\d\]|\[Orion_Me\]|\[ORLY\]|Please\.\.\.|R4 - Book of the Week|Re: |READNFO|Req: |Req\.|!<-- REQ:|^Request|Requesting|Should I continue posting these collections\?|\[Team [a-z0-9]+\]|[\.\-_ ](Thanks|TIA!)[\.\-_ ]|\(v\.?\d+\.\d+[a-z]?\)|par2 set/i', ' ', $cleanerName);
        if($debug)
            echo "4 - ".$cleanerName."\n";
		//Unwanted stuff.
		$cleanerName = preg_replace('/sample("| )?$|"sample|\(\?\?\?\?\)|\[AoU\]|AsianDVDClub\.org|AutoRarPar\d{1,5}|brothers\-of\-usenet\.(info|net)(\/\.net)?|~bY ([a-z]{3,15}|c-w)|By request|DVD-Freak|Ew-Free-Usenet-\d{1,5}|for\.usenet4ever\.info|ghost-of-usenet.org<<|GOU<<|(http:\/\/www\.)?friends-4u\.nl|\[\d+\]-\[abgxEFNET\]-|\[[a-z\d]+\]\-\[[a-z\d]+\]-\[FULL\]-|\[\d{3,}\]-\[FULL\]-\[(a\.b| abgx).+?\]|\[\d{1,}\]|\-\[FULL\].+?#a\.b[\w.#!@$%^&*\(\){}\|\\:"\';<>,?~` ]+\]|Lords-of-Usenet(\] <> presents)?|nzbcave\.co\.uk( VIP)?|(Partner (of|von) )?SSL\-News\.info>> presents|\/ post: |powere?d by (4ux(\.n\)?l)?|the usenet)|(www\.)?ssl-news(\.info)?|SSL - News\.Info|usenet-piraten\.info|\-\s\[.+?\]\s<>\spresents|<.+?https:\/\/secretusenet\.com>|SECTIONED brings you|team-hush\.org\/| TiMnZb |<TOWN>|www\.binnfo\.in|www\.dreameplace\.biz|wwwworld\.me|www\.town\.ag|(Draak48|Egbert47|jipenjans|Taima) post voor u op|Dik Trom post voor|Sponsored\.by\.SecretUsenet\.com|(::::)?UR-powered by SecretUsenet.com(::::)?|usenet4ever\.info|(www\.)?usenet-4all\.info|www\.torentz\.3xforum\.ro|usenet\-space\-cowboys\.info|> USC <|SecretUsenet\.com|Thanks to OP|\] und \[|www\.michael-kramer\.com|(http:\\\\\\\\)?www(\.| )[a-z0-9]+(\.| )(co(\.| )cc|com|info|net|org)|zoekt nog posters\/spotters|>> presents|Z\[?NZB\]?(\.|_)wz(\.|_)cz|partner[\.\-_ ]of([\.\-_ ]www)?/i', ' ', $cleanerName);
        if($debug)
            echo "5 - ".$cleanerName."\n";
        //Replaces some characters with 1 space.
        // Removed the apostrophe - 0827
        $cleanerName = str_replace(array(".", "_", '-', "|", "<", ">", '"', "=", '[', "]", "(", ")", "{", "}", "*", ";", ":", ",", "~", "/", "&", "+"), " ", $cleanerName);
        if($debug)
            echo "6 - ".$cleanerName."\n";
        // More unwanted crap
        $cleanerName = preg_replace('/as requested|\d{5,10}|nmr|repack|AS REQ |by req |2nd try |per req |REPOST |THEATRICAL |AKA |\d{2,} (?=.+(20|19)\d\d)|feature films/i', '', $cleanerName);
        if($debug)
            echo "7 - ".$cleanerName."\n";
		//Change [pw] to passworded.
		$cleanerName = str_replace(array('[pw]', '[PW]', ' PW ', '(Password)'), ' PASSWORDED ', $cleanerName);
        if($debug)
            echo "8 - ".$cleanerName."\n";



		//Replace multiple spaces with 1 space
		$cleanerName = trim(preg_replace('/\s\s+/i', ' ', $cleanerName));
        if($debug)
            echo "9 - ".$cleanerName."\n";
		// Taking out the double name thing.  It really causes a lot of problems with book, music, and movie lookups later on
        // Also, if the regexes in searchnameRegex table are working, you shouldn't really see a lot of double names
		//Remove the double name.
		// $cleanerName = implode(' ', array_intersect_key(explode(' ', $cleanerName), array_unique(array_map('strtolower', explode(' ', $cleanerName)))));

        // Wrote a new 'double name' function.  I think this will work much better.
        $cleanerName = $this->removeDoubleName($cleanerName);

        if($debug)
            echo "10 - ".$cleanerName."\n";
		if (empty($cleanerName)) {return $subject;}
		else {return trim($cleanerName);}
	}

    public function removeDoubleName($text)
    {
        if(empty($text) || strlen($text)<10)
            return $text;

        $textLowerCase = strtolower($text);
        $words = explode(' ',$textLowerCase);
        $numWords = count($words);

        if ($numWords <= 5)
            return $text;

        for($count=0; $count<$numWords-1; $count++)
        {
            $firstPos = strpos($textLowerCase,($words[$count].' '.$words[$count+1]));
            $secondPos = strrpos($textLowerCase,($words[$count].' '.$words[$count+1]));
            if($secondPos>$firstPos)
            {
                $text1 = substr($text, 0, $secondPos);
                $text2 = substr($text, $secondPos);
                $text = (strlen($text1)>strlen($text2)) ? $text1 : $text2;
                break;
            }
        }

        return $text;
    }
	//
	//	Cleans release name for the namefixer class.
	//
	public function fixerCleaner($name, $leaveFileExtension=false)
	{
		//Extensions.
        if ($leaveFileExtension)
            $cleanerName = $name;
        else
		    $cleanerName = preg_replace('/ [a-z0-9]+\.jpg|((\d{1,3})?\.part(\d{1,5})?|\d{1,5}|sample)?\.(7z|\d{3}(?=(\s|"))|avi|epub|idx|iso|jpg|m4a|mds|mkv|mobi|mp4|nfo|nzb|pdf|rar|rev|rtf|r\d\d|sfv|srs|srr|sub|txt|vol.+(par2)|par(\s?2|")|zip|z{2})"?|(\s|(\d{2,3})?\-)\d{2,3}\.mp3|\d{2,3}\.pdf|yEnc|\.part\d{1,4}\./i', ' ', $name);
		// Even if we skip removing file extensions above, still should remove '.nfo' as that extension tells us nothing about the type of release
        $cleanerName = preg_replace('/\.nfo$/', '', $cleanerName);
		//Replaces some characters with 1 space.
		// Going to leave in apostrophes and commas and see if that creates any issues.  Not having them is causing issues with
        // release lookups (console, book, music, movies)
        $cleanerName = str_replace(array(".", "_", '-', "|", "<", ">", '"', "=", '[', "]", "(", ")", "{", "}", "*", ";", ":", "~", "/", "&", "+"), " ", $cleanerName);
		//Replace multiple spaces with 1 space
		$cleanerName = preg_replace('/\s\s+/i', ' ', $cleanerName);
		//Remove Release Name
		$cleanerName = trim(preg_replace('/^Release Name/i', ' ', $cleanerName));
		//Remove annoying prefixes
		$cleanerName = trim(preg_replace('/download all our files with |www allyourbasearebelongtous|^id \d{1,2}|download all our files with|repost|as req/i', '', $cleanerName));
		//Remove invalid characters.
		$cleanerName = trim(utf8_encode(preg_replace('/[^(\x20-\x7F)]*/','', $cleanerName)));

		return trim($cleanerName);
	}

    public function cleanUnicode($text)
    {
        return htmlspecialchars_decode(htmlspecialchars($text,ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE));
    }

    public function movieCleaner($text)
    {
        // Function for further cleaning up movie names before attempting to search for them on TMDb or IMDb
        if( $text == NULL || $text == '')
            return false;
        // First group is case insensitive
        $text = preg_replace('/^have fun|http lostmoviearchives com( movie)?( thumbs)?|walt disney|walt disney.s|director s cut|directors cut|TGS|E4S|RE UP |^RS |mp4a|unrated |repack |dubbed |subtitled |extended cut |x264 \w+$|x264 |englisch/i', '', $text);
        $text = preg_replace('/NTSC|MOViEONLY|DVD(5|9)|F0RFUN|www allyourbasearebelongtous pw |DVDR|ANiPUNK(.+)?|Mayhem|AN0NYM0US(.+)?|EwDp|unrated|norbit|www drlecter tk | R\d|(\-)?ironclub/i', '', $text);
        $text = preg_replace('/DAMiANA|1098JHWOTNGS|xvid([\- ]\w+$)?|(dvd|bd)rip|(\-)?AN0NYM0US( CD)?|sample/i', '', $text);
        // Second group is case sensitive
        $text = preg_replace('/FILL|AmA (DIVX|XviD)|PROPER |1080p|720p|480p|AVC|(H|h)264|PAL|iNT|COMPLETE|LIMITED|MASTER|iOM|SAM|RETAIL|MADE|NZBGRABIT LOWERS TONE AGAIN PAY PER DOWNLOAD/', '', $text);
        // NTSC DVDR MADE NZBGRABIT LOWERS TONE AGAIN PAY PER DOWNLOAD 0 1098JHWOTNGS
        return $text;

    }

    public function musicCleaner($text)
    {
        $newname = preg_replace('/ (\d{1,2} \d{1,2} )?(Bootleg|Boxset|Clean.+Version|Compiled by.+|\dCD|Digipak|DIRFIX|DVBS|FLAC|(Ltd )?(Deluxe|Limited|Special).+Edition|Promo|PROOF|Reissue|Remastered|REPACK|RETAIL(.+UK)?|SACD|Sampler|SAT|Summer.+Mag|UK.+Import|Deluxe.+Version|VINYL|WEB)/i', ' ', $text);
        $newname = preg_replace('/ ([a-z]+[0-9]+[a-z]+[0-9]+.+|[a-z]{2,}[0-9]{2,}?.+|3FM|B00[a-z0-9]+|BRC482012|H056|UXM1DW086|(4WCD|ATL|bigFM|CDP|DST|ERE|FIM|MBZZ|MSOne|MVRD|QEDCD|RNB|SBD|SFT|ZYX) \d.+)/i', ' ', $newname);
        $newname = preg_replace('/ (\d{1,2} \d{1,2} )?([A-Z])( ?$)|[0-9]{8,}| (CABLE|FREEWEB|LINE|MAG|MCD|YMRSMILES)/', ' ', $newname);
        $newname = preg_replace('/VA( |-)/', 'Various Artists ', $newname);
        $newname = preg_replace('/ (\d{1,2} \d{1,2} )?(DAB|DE|DVBC|EP|FIX|IT|Jap|NL|PL|(Pure )?FM|SSL|VLS) /i', ' ', $newname);
        $newname = preg_replace('/ (\d{1,2} \d{1,2} )?(CD(A|EP|M|R|S)?|QEDCD|SBD) /i', ' ', $newname);
        $newname = trim(preg_replace('/\s\s+/', ' ', $newname));
        $newname = trim(preg_replace('/ [a-z]{2}$| [a-z]{3} \d{2,}$|\d{5,} \d{5,}$/i', '', $newname));
        // Below tries to catch a lot of the crap that shows up before and after the title
        $newname = trim(preg_replace('/music$/i', '', $newname));
        $newname = trim(preg_replace('/_|\./', ' ', $newname));
        $newname = trim(preg_replace('/download all our files with|illuminatenboard org|MP3|#a b|inner sanctum@EFNET|[a-z]{1,10}@EFNET|Gate [0-9]{1,2} [0-9]{2,12}|kere ws|[0-9]{3,12}|#altbin@EFNet|www Thunder News org|usenet of inferno us|TOWN|SEK9 FLAC Hip Hop|SEK9 FLAC [A-Za-z]{4,10}|powerd by getnzb com|Wildrose [0-9]{3,6}|DREAM OF USENET INFO|http dream of usenet info/', '', $newname));
        $newname = trim(preg_replace('/ \( |CD FLAC|[A-Z]{3,12}| 0{2,4}| proof| dl| m3u|2Eleven|[A-Z]{1,8}[0-9]{1,3}|by Secretusenet|[0-9]{3}|DeVOiD|k4|[3-9][0-9]{2,}|FiH|LoKET|SPiEL|[A-Z]{3,}[0-9]{1,4}CD|flacme|nmr@VBR apex 00|12 Vinyl|[0-9]{2,3} kbps|CBR 00/', '', $newname));
        $newname = trim(preg_replace('/-[A-Za-z]{3}-[0-9]{2}-[0-9]{2}-|--[0-9]{2}-[0-9]{2}|\w{2}-[A-Za-z]{2,3}-[0-9]{2}-[0-9]{2}|\d\d-\d\d-|-cd-|\(Proton Radio\)|\(Pure FM\)|-[A-Za-z]{2,3}-web-|-web-|\(Maxima FM\)|trtk|Complete Sun records singles/i', '', $newname));
        $newname = trim(preg_replace('/\($/', '', $newname));  // Get rid of extra trailing '(' that shows up for some reason

        // End crap trimming
        return $newname;

    }

    public function consoleCleaner($text, $categoryID)
    {
        // TODO: Add regex for removing strings of letters/numbers (like a hash)
        // TODO: Add cleaner code for DLC (low priority)

        $debug = false;
        $newname = '';
        // NDS stuff
        $text = preg_replace('/\./', ' ', $text );
        if($categoryID==1010)
        {
            preg_match('/(.+NDS)[\- ].+/i', $text, $matches);
            if(isset($matches[1]))
                $newname=$matches[1];
            else
            {
                preg_match('/(.+3DS).+/i', $text, $matches);
                if(isset($matches[1]))
                    $newname=$matches[1];
                else
                    $newname=$text;
            }
            $newname = preg_replace('/^\d{4}||\x27\d{4}|multi(\d{1,2})?|(EU(R)?|US|JP(N)?|KS|DE|IT|FR|NL|M\d|AU) games|v\d{1,2}|(19|20)\d\d|multi$|Intro/i', '', $newname);
            $newname = preg_replace('/\d{3} mb|trimmed|place2home net (\d{4})?|FULL \#abgx\@EFNET|(FULL )?ABGX( net)?( FULL)?|Shadowman|\@efnet|P2H|wildrose|^\# \w+ | \d$/i', '', $newname);
            $newname = preg_replace('/readnfo|read nfo|description|repack|nintendo |^snake|nintendo ds \d{4}|DS Roms z\d{1,4} z\d{1,4}( \d{4})?|internal/i', '', $newname);
            $newname = preg_replace('/Europe( En)?( Es)?( Sv)?( No)?( Da)?( Fi)?|Rev \d|enhanced( [A-Za-z])?|dubbed|ver |www Thunder[\- ]News org/i', '', $newname);
            $newname = preg_replace('/Sponsored by Secretusenet ctr ap9p|There\x27s no place like|\#a b g nintendods|proper|Disney Games|ABSTRAKT|P2H as(.+)$|the dsi/i', '', $newname);
            // following groups are case sensitive
            $newname = preg_replace('/PAL|JAP|usa|GER|EU(R)?|US(A)?|JP(N)?|KS|DE|IT|FR|NL|M2|M6|M4|M3|M5|M7|DSi|NINTENDO|CLEAN|clean|NDSi|[A-Z]{4,6} |CONTRAST/','', $newname);
            // hash-like strings (8 or more chars, must be alpha AND numeric)
            $newname = preg_replace('/(?=[a-zA-Z0-9]*?[A-Za-z])(?=[a-zA-Z0-9]*?[A-Za-z])(?=[a-zA-Z0-9]*?[0-9])[a-zA-Z0-9]{8,}/', '', $newname);
            if(!preg_match('/NDS$/', $newname) && !preg_match('/3DS$/', $newname))
                $newname = $newname.' NDS';
        }
        // PS3 stuff
        if($categoryID==1080)
        {
            preg_match('/(.+PS3)[\- ].+/i', $text, $matches);
            if(isset($matches[1]))
                $newname=$matches[1];
            else
                $newname=$text;
            if($debug)
                echo "Category ID: ".$categoryID." Newname: ".$newname."\n";
            $newname = preg_replace('/(FULL )?ABGX( net)?( FULL)?|abgx net|abgx|abgx full|abgx net full|clandestine|clan raca|place2home( net)?( duplex)?|description|duplex|HR hpatdh1u/i', '', $newname);
            $newname = preg_replace('/SweeTpS3|proton| ps3(?=PS3)|CLARE clp3 tovj|SweeTpS3 sweet maps|Caravan cvn qwetjb|\@efnet|ohne |Caravan cvn ogs|MOEMOE |moe ffxiii|STORMAN|HR |german |ASiA |multi\d{1,2}| PROPER/i', '', $newname);
            $newname = preg_replace('/repack|dubbed|ver |1080|EBOOT|PATCH( \d\d\d)?( TB)?|www Thunder[\- ]News org|readnfo|read nfo|repack|JB-PEMA|FW\d\d\d|UPDATE|v\d (\d\d)?|\d \d\d/i', '', $newname);
            $newname = preg_replace('/2continue org|info|^snake|(19|20)\d\d|Sponsored by Secretusenet(.+)?$/i', '', $newname);
            // The following groups are case sensitive
            $newname = preg_replace('/JB|MRN|PSN|US(A)?|JPN|JAP|PAL|EU(R)?|SPANiSH|ENGLiSH|UNCENSORED|USENET(-TURK)?|NTSC|Asian|FIX|PS |UPDATE|FIX for CFW|(V|v)\d/', '', $newname);
            // hash-like strings (8 or more chars, must be alpha AND numeric)
            $newname = preg_replace('/(?=[a-zA-Z0-9]*?[A-Za-z])(?=[a-zA-Z0-9]*?[A-Za-z])(?=[a-zA-Z0-9]*?[0-9])[a-zA-Z0-9]{8,}/', '', $newname);
            if(!preg_match('/PS3$/', $newname))
                $newname = $newname.' PS3';
        }
        // PSP stuff
        if($categoryID==1020)
        {
            preg_match('/(.+PSP)[\- ].+/i', $text, $matches);
            if(isset($matches[1]))
                $newname=$matches[1];
            else
                $newname=$text;
            if($debug)
                echo "Category ID: ".$categoryID." Newname: ".$newname."\n";
            $newname = preg_replace('/PSX2PSP|EBOOT|pspking(.+)?$|USA |ABGX( net)?( FULL)?|\@efnet|JPN|JAP|EUR|USA|PSN|(ZERO|ZER0)|www realmom| CHT | NRP(.+)?$| CLARE(.+)?$|\-PLAYASiA\-4/i', '', $newname);
            $newname = preg_replace('/trailer |PSXPSP(.+)?$|PSX|(\-)?PAL(\-)?|R3ds|(full )?(working )?UMDRIP|read nfo|proper|\-episode|UMD|readnfo|disc (\d )+/i', '', $newname);
            // The following groups are case sensitive
            $newname = preg_replace('/EUR|JB|MRN|PSN|USA|JPN|JAP|PAL/', '', $newname);
            if(!preg_match('/PSP$/', $newname))
                $newname = $newname.' PSP';

        }
        // Wii Stuff
        if($categoryID==1030)
        {
            preg_match('/(.+WII)[\- ].+/i', $text, $matches);
            if(isset($matches[1]))
                $newname=$matches[1];
            else
                $newname=$text;
            if($debug)
                echo "Category ID: ".$categoryID." Newname: ".$newname."\n";
            $newname = preg_replace('/(19|20)\d\d|ABGX( net)?( FULL)?|as disir |place2home |PAL |USA |REPACK|MULTi\d{1,2}.+$|RARFIX| int|German |\d \d{1,2} |NGC |WORKING( internal)?( for)?|repack|NZBSRUS| ind fixed copy protection(.+)?$/i', '', $newname);
            $newname = preg_replace('/SPANiSH|abgx EFNET FULL|FULL \#abgwii\@EFNet|(19|20)\d\d|^Department|\#a b g wii\@efnet|www newsconnection eu|format/i', '', $newname);
            // The following groups are case sensitive
            $newname = preg_replace('/EUR|JB|MRN|PSN|USA|JPN|JAP|PAL|WBFS/', '', $newname);
            if(!preg_match('/WII$/i', $newname))
                $newname = $newname.' WII';
        }
        // XBOX stuff
        if($categoryID==1040)
        {
            preg_match('/(.+XBOX)[\- ].+/i', $text, $matches);
            if(isset($matches[1]))
                $newname=$matches[1];
            else
                $newname=$text;
            if($debug)
                echo "Category ID: ".$categoryID." Newname: ".$newname."\n";
            $newname = preg_replace('/PAL |USA |REPACK|MULTi\d{1,2}.+$|MULTI|DVD\-.+$|PAL\-.+$|DVD\-RIP|Games XBOX |NTSC|wildrose/i', '', $newname);
            if(!preg_match('/XBOX$/i', $newname))
                $newname = $newname.' XBOX';
        }
        // XBOX 360 Stuff
        if($categoryID==1050)
        {
            preg_match('/(.+XBOX360)[\- ].+/i', $text, $matches);
            if(isset($matches[1]))
                $newname=$matches[1];
            else
            {
                preg_match('/(.+)X360.+/i', $text, $matches);
                if(isset($matches[1]))
                    $newname=$matches[1];
                else
                    $newname=$text;
            }
            if($debug)
                echo "Category ID: ".$categoryID." Newname: ".$newname."\n";
            $newname = preg_replace('/imars|XBOX |ISORIP|ABGX( net)?( FULL)?( complex)?|ABGX net unlimited|place2home( net)?( complex)?|USA (RF)?|full abgx net|German|P AL|PAL|UNCUT|read( nfo)?|NTSC|^XBOX 360 /i', '', $newname);
            if(!preg_match('/XBOX360$/i', $newname))
                $newname = $newname.' XBOX360';

        }

        if(strlen($newname)>6)
        {
            if($debug)
                echo "Returning: ".$newname."\n";
            return trim($newname);
        }
        else
            return $text;
    }

    public function bookCleaner($text)
    {

        $newname = preg_replace('/\d{1,2} \d{1,2} \d{2,4}|(19|20)\d\d|anybody got .+?[a-z]\? |[\.\-_ ](Novel|TIA)([\.\-_ ]|$)|( |\.)HQ(-|\.| )|[\(\)\.\-_ ]?(DOC|EPUB|LIT|MOBI|NFO|(si)?PDF|RTF|TXT)(?![a-z0-9])/i', '', $text);
        $newname = preg_replace('/compleet|DAGSTiDNiNGEN|DiRFiX|\+ extra|more ebooks|r?e ?Books?([\.\-_ ]English|ers)?|ePu(b|p)s?|html|mobi|^NEW[\.\-_ ]/i', '', $newname);
        $newname = preg_replace('/PDF([\.\-_ ]English)?|Please post more|Post description|Proper|Repack(fix)?|[\.\-_ ](Chinese|English|French|German|Italian|Retail|Scan|Swedish)/i', '', $newname);
        $newname = preg_replace('/^R4 |Repost|Skytwohigh|TIA!+|TruePDF|V413HAV|(would someone )?please (re)?post.+? "|with the authors name right|^Wildrose|e books \d{1,2} (20|19)\d\d/i', '', $newname);
        $newname = preg_replace('/^(As Req |conversion |eq |Das neue Abenteuer \d+|Fixed version( ignore previous post)?|Full |Per Req As Found|(\s+)?R4 |REQ |revised |version |\d+(\s+)?$)/i', '', $newname);
        $newname = preg_replace('/(COMPLETE|INTERNAL|RELOADED| (AZW3|eB|docx|ENG?|exe|FR|Fix|gnv64|MU|NIV|R\d\s+\d{1,2} \d{1,2}|R\d|Req|TTL|UC|v(\s+)?\d))(\s+)?$/i', '', $newname);
        $newname = preg_replace('/\#altbin\@efnet FULL |PRODEV(.+)$|pdb|As( PDF)? only Use CALIBRE to convert \! |^\d{3,5}|all i have|Softarchive net|htm(l)?|^[a-z]+ post/i','', $newname);
        $newname = preg_replace('/^Young Adult|In response to REQ hope this helps|fixed TOC and tweaked|Looking for (1st|2nd|3rd|4th|5th|6th|7th|8th|9th) book|all i could find/i', '', $newname);
        $newname = preg_replace('/jpg|thanku|^Various|et al|\w+\@\w+ (org|com|edu|net)|v\d( \d{1,2})?/i', '', $newname);
        // Following group is case sensitive
        $newname = preg_replace('/(EU(R)?|US|JP(N)?|KS|DE|IT|FR|NL|M\d|AU|SD)|HERES|ATTN( [A-Za-z0-9]+)? |ARC|HQ/', '', $newname);

        $newname = trim(preg_replace('/\s\s+/i', ' ', $newname));


        return $newname;
    }
}
