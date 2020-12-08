<?php


namespace App\Helper\Parser;


use IvoPetkov\HTML5DOMDocument;

class GKB
{
    public $domain = 'https://gkb81.ru';
    public $pathToAdvice = '/sovety';

    public function getHtmlAdvice() {
        $htmlAdvice = new HTML5DOMDocument();
        $htmlAdvice->loadHTMLFile($this->domain . $this->pathToAdvice, LIBXML_NOERROR);
        return $htmlAdvice;
    }

    public function getPathToAdvicePage($page) {
        return $this->pathToAdvice . '/page/' . $page;
    }

    public function getPages($html) {
        $result = [];
        foreach ($html->querySelectorAll('.pagination.loop-pagination .page-numbers') as $h3) {
            if ($h3->nodeValue) $result[] = $h3->nodeValue;
        }
        return $result;
    }

    private function getArticlesFromPage($html) {
        $result = [];
        foreach ($html->querySelectorAll('article') as $article) {
            $articleValues['title'] = $article->querySelector('h3')->nodeValue;
            $articleValues['date'] = $article->querySelector('.dt')->nodeValue;
            $articleValues['link'] = $article->querySelector('a')->getAttribute('href');
            $articleValues['image'] = $article->querySelector('img')->getAttribute('src');
            $result[] = $articleValues;
        }
        return $result;
    }

    private function getFullTextArticle(&$article) {
        $html = new HTML5DOMDocument();
        $html->loadHTMLFile($article['link'], LIBXML_NOERROR);
        $article['text'] = $html->querySelector('.description .text')->getTextContent();
    }

    public function getAllArticles() {
        $html = $this->getHtmlAdvice();
        $numberOfPage = $this->getPages($html);

        $articles = [];
        foreach ($numberOfPage as $page) {
            $html = new HTML5DOMDocument();
            $html->loadHTMLFile($this->domain . $this->getPathToAdvicePage($page), LIBXML_NOERROR);
            $articles = array_merge($articles, $this->getArticlesFromPage($html));
        }

        foreach ($articles as &$article) {
           $this->getFullTextArticle($article);
        }

        return $articles;
    }
}
