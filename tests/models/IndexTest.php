<?php

namespace crawlerests\models;

use luya\crawler\models\Index;
use crawlerests\CrawlerTestCase;
use crawlerests\data\fixtures\IndexFixture;

class IndexTest extends CrawlerTestCase
{
    public function testPreview()
    {
        $model = new Index();
        $model->content = 'Wohn- und B&uuml;rozentrum f&uuml;r K&ouml;rperbehinderte Die F&auml;higkeit z&auml;hlt, nicht die Behinderung Unterst&uuml;tzen Sie uns Geldspenden, Freiwilligenarbeit oder Partnerschaften &ndash; jegliche Form von Unterst&uuml;tzung ist herzlich willkommen.             Kunst aus dem kreativAtelier Vernissage im Lichthof: 6.12.2016, 9.30 bis 10.30 UhrAusstellung: 6.12.2016 bis 15.01.2017             Kunstausstellung Carina Tschan Vernissage im Lichthof: 20.1.2017, 14 bis 15.30 UhrAusstellung: 20.1. bis 24.3.2017             Dienstleistungen / Produkte f&uuml;r Kunden         Leistungen f&uuml;r Menschen mit Behinderung         Unterst&uuml;tzung f&uuml;r Spendende und Freiwillige         WBZ-Flohmarkt 2016 mit Jazz-Matin&eacute;e Am Freitag, 28. Oktober 2016, heisst es wieder auf die Pl&auml;tze, fertig, WBZ-Flohmarkt!  Aktuell       Neubau        6.12.2016 - 15.1.2017 - Ausstellung Kunst aus dem kreativAtelier Unter der Leitung von Marion Gregor ist im WBZ inspirierende Kunst entstanden. Die Kunstwerke werden im Lichthof (Aumattstrasse 71, 4153 Reinach) ausgestellt und zum Verkauf angeboten.  Events         &Uuml;ber uns       Tageskarte Restaurant Albatros        Stellen       WBZ-Imagefilm       WBZ-Flohmarkt Aufbau       WBZ-Flohmarkt Abbau       WBZ - Wohn- und B&uuml;rozentrum f&uuml;r K&ouml;rperbehinderte';

        $this->assertContains('z&auml;<span style="background-color:#FFEBD1; color:black;">hlt</span>', $model->preview('hlt', 150));
        $this->assertSame('..lo foobar He..', $model->cut("foobar", "Hello foobar Hello", 3));
        $this->assertSame('Hello <span style="background-color:#FFEBD1; color:black;">foobar</span> Hello', $model->highlight('foobar', 'Hello foobar Hello'));
        $this->assertSame('Hello <span style="background-color:#FFEBD1; color:black;">FOOBAR</span> Hello', $model->highlight('foobar', 'Hello FOOBAR Hello'));
        $this->assertSame('Hello <span style="background-color:#FFEBD1; color:black;">foobar</span> Hello', $model->highlight('FOOBar', 'Hello foobar Hello'));
    
        $this->assertSame('Wohn- und B&uuml;rozentrum f&uuml;r K&ouml;rperbehinderte Die F&auml;higkeit z&auml;hlt, nicht die Behinderung Unterst&uuml;tzen Sie uns Geldspenden, Freiwilligenarbeit oder Partnerschaften &ndash; jegliche Form von Unterst&uuml;tzung ist herzlich willkommen.             Kunst aus dem kreativAtelier..', $model->preview('notexisting'));
    }
    
    public function testFlatSearchByQuery()
    {
        $test = Index::flatSearchByQuery('aaa', 'en');
        $this->assertSame('aaa', $test[0]->title);
        
        $test = Index::flatSearchByQuery('AAA', 'en');
        $this->assertSame('aaa', $test[0]->title);
        
        $test = Index::flatSearchByQuery('bbb', 'en');
        $this->assertSame('aaa', $test[0]->title);
        
        $test = Index::flatSearchByQuery('ccc', 'en');
        $this->assertSame('aaa', $test[0]->title);
    }
    
    public function testsearchByQuery()
    {
        $test1 = Index::searchByQuery('aaa', 'en');
        $this->assertSame('aaa', $test1[0]->title);
        $test1 = Index::searchByQuery('AAA', 'en');
        $this->assertSame('aaa', $test1[0]->title);
    
        $test2 = Index::searchByQuery('bbb', 'en');
        $this->assertSame('aaa', $test2[0]->title);
    
        $test3 = Index::searchByQuery('ccc', 'en');
        $this->assertSame('aaa', $test3[0]->title);
    }
    
    public function testEnhancedSearchByQuery()
    {
        $test1 = Index::searchByQuery('drink bug', 'en');
        $this->assertSame('index2', $test1[0]->title);
        
        $test1 = Index::searchByQuery('Drink BUG', 'en');
        $this->assertSame('index2', $test1[0]->title);
        
        $test2 = Index::searchByQuery('drinking finding', 'en');
        $this->assertSame('index3', $test2[0]->title);
        
        // test4
        $test3 = Index::searchByQuery('two words', 'en');
        $this->assertSame('index4', $test3[0]->title);
        
        $test4 = Index::searchByQuery('words two', 'en');
        $this->assertSame('index4', $test4[0]->title);
        
        $test5 = Index::searchByQuery('words two', 'en');
        $this->assertSame('index4', $test5[0]->title);
        
        $test6 = Index::searchByQuery('words two three', 'en');
        $this->assertEmpty($test6);
    }
    
    public function testEmptySearchs()
    {
        $test1 = Index::searchByQuery('1', 'en');
        $this->assertEmpty($test1);
        $test2 = Index::flatSearchByQuery('1', 'en');
        $this->assertEmpty($test2);
    }
    
    public function testSortByUrl()
    {
        $test1 = Index::searchByQuery('item', 'en');
        $this->assertSame(3, count($test1));
        
        $this->assertSame('index5/item', $test1[1]->url);
        $this->assertSame('index6/else/item', $test1[0]->url);
        $this->assertSame('index7.php', $test1[2]->url);
    }
    
    public function testSameSortByUrl()
    {
        $test1 = Index::searchByQuery('index', 'en');
    
        $this->assertSame(6, count($test1));
    
        $this->assertSame('index6/else/item', $test1[0]->url);
        $this->assertSame('index5/item', $test1[1]->url);
        $this->assertSame('index2.php', $test1[2]->url);
    }

    public function testWithMultipleWordsAndPreview()
    {
        $test1 = Index::searchByQuery('barfoo drink', 'en');
        $this->assertSame(2, count($test1));
    }

    public function testHtmlEncodingQuery()
    {
        $this->assertSame('Öff', Index::encodeQuery('Öff'));
        
        $this->assertSame(1, (int) Index::activeQuerySearch('öff', 'de')->count());
        $this->assertSame(1, (int) Index::activeQuerySearch('Öff', 'de')->count());
        
        $this->assertSame('offnungszeiten', Index::searchByQuery('öff', 'de')[0]->title);
        $this->assertSame('offnungszeiten', Index::searchByQuery('Öff', 'de')[0]->title);
    }

    public function testSpecialCharsHighlightBug()
    {
        $index = new Index();
        $index->content = 'Team Gare du Nord K&uuml;nstlerische Leitung: D&eacute;sir&eacute;e MeiserGesch&auml;ftsf&uuml;hrung: Ursula FreiburghausK&uuml;nstlerische Betriebsleitung, Vermittlung: Johanna SchweizerTechnik: Mario Henkel, Jean-Marc DesbonnetsPresse- und &Ouml;ffentlichkeitsarbeit: Ph&ouml;be HeydtKontaktstelle Publikumsvermittlung/Administration: Francesca Dunkel, Johanna K&ouml;hler (Mutterschaftsvertretung Spielzeit 2018/19)Privatvermietung: Maya ZimmermannPraktikum: Jenny LehmannGrafik: Alexa Fr&uuml;h Konzert- und Projektanfragen Vorschl&auml;ge f&uuml;r k&uuml;nstlerische Arbeiten k&ouml;nnen an die k&uuml;nstlerische Leitung eingereicht werden. Wir bitten jedoch um Verst&auml;ndnis, dass nicht jede Anfrage beantwortet und dass unaufgefordert eingesandtes Material nicht zur&uuml;ckgeschickt werden kann. Bar du Nord Gesch&auml;ftsf&uuml;hrung: Bruno ZihlmannT +41 61 683 71 70     Der Programmrat des Gare du Nord Der Programmrat des Tr&auml;gervereins Gare du Nord hat k&uuml;nstlerische Beratungsfunktion und besteht aus wichtigen Vertretern der zeitgen&ouml;ssischen Musikszene: Aktive Mitglieder:  J&uuml;rg Hennenberger, Dirigent, Pianist und Dozent an der Hochschule f&uuml;r Musik FHNW Michael Kunkel, Leiter der Abteilung Forschung und Entwicklung der Hochschule f&uuml;r Musik FHNW Marcus Weiss, Saxophonist und Musiker. Professor an der Hochschule f&uuml;r Musik FHNW, Leitung Master f&uuml;r Zeitgen&ouml;ssische Musik (Performance) D&eacute;sir&eacute;e Meiser, K&uuml;nstlerische Leiterin Gare du Nord  Passive Mitglieder:  Wolfgang Heiniger, Professor f&uuml;r Intermediale Komposition an die Hochschule f&uuml;r Musik Hanns Eisler Berlin Ute Haferburg, Leiterin des Theater Chur, ehemalige K&uuml;nstlerische Co-Leiterin und Gesch&auml;ftsf&uuml;hrerin Gare du Nord        Event SitemapTeam | Gare du Nord';

        $this->assertContains('<span style="background-color:#FFEBD1; color:black;">Ph&ouml;be</span>', $index->preview('ph&ouml;be'));
    }
}
