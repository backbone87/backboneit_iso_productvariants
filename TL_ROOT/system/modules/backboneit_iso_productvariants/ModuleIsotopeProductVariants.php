<?php

class ModuleIsotopeProductVariants extends ModuleIsotopeProductVariantList {

	public function generate() {
		if(TL_MODE == 'BE') {
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: PRODUCT VARIANTS ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}
		
		$this->blnCacheProducts = false;

		if($this->Input->get('product') == '') {
			return '';
		}
		
		return parent::generate();
	}

	protected function findProducts($arrCacheIds = null) {
		list($arrFilters, $arrSorting, $strWhere, $arrValues) = $this->getFiltersAndSorting();
		
		if(!BE_USER_LOGGED_IN) {
			$intTime = time();
			$strPublish = '
				AND p1.published = \'1\'
				AND (p1.start = \'\' OR p1.start < ' . $intTime . ')
				AND (p1.stop = \'\' OR p1.stop > ' . $intTime . ')
			';
		}
		
// 		is_array($arrCacheIds) && $strCache = 'AND ('
// 			. '	p1.id IN (' . implode(',', $arrCacheIds) . ')' .
// 			. ' OR ' .	
// 			. ' p1.pid IN (' . implode(',', $arrCacheIds) . ')' .
// 		')';
		
		$this->iso_list_where && $strListWhere = 'AND ' . $this->iso_list_where;
		
		$strAlias = $this->Input->get('product');
		$strIDField = is_numeric($strAlias) ? 'id' : 'alias';
		array_unshift($arrValues, $strAlias);
		
		$objProductData = $this->Database->prepare(
			IsotopeProduct::getSelectStatement() . '
			JOIN	tl_iso_products AS p3 ON p3.id = p1.pid
			WHERE	p1.language = \'\'
			AND		p3.' . $strIDField . ' = ?
			' . $strPublish . '
			' . $strCache . '
			' . $strListWhere . '
			' . $strWhere . '
			GROUP BY p1.id
			ORDER BY c.sorting'
		)->execute($arrValues);
		
		return IsotopeFrontend::getProducts($objProductData, 0, true, $arrFilters, $arrSorting);
		
	}

}
