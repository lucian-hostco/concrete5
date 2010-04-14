<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));
$u = new User();

$sh = Loader::helper('concrete/dashboard/sitemap');
if (!$sh->canRead()) {
	die(t('Access Denied'));
}

Loader::model('attribute/categories/collection');
$cnt = Loader::controller('/dashboard/sitemap/search');
$pageList = $cnt->getRequestedSearchResults();

$pages = $pageList->getPage();
$pagination = $pageList->getPagination();


Loader::element('pages/search_results', array('pages' => $pages, 'pageList' => $pageList, 'pagination' => $pagination));