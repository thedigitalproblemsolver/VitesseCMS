<?php

namespace VitesseCms\Shop\Controllers;

use VitesseCms\Content\Models\Item;
use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Core\Factories\ObjectFactory;

/**
 * Class AdminstockController
 */
class AdminstockController extends AbstractAdminController
{

    /**
     * checkAction
     */
    public function checkAction(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MinimalinStock.csv');
        $output = fopen('php://output', 'wb');
        fputcsv($output, [
            'name',
            'sku',
            'printId',
            'stock',
            'stockMinimal',
            'amount to order'
        ]);

        Item::setFindValue('availableForReselling', '1');
        Item::addFindOrder('gender',-1);
        Item::addFindOrder('name');
        $items = Item::findAll();
        foreach ($items as $item) :
            $totalItems = 0;
            $gender = ObjectFactory::create();
            if($item->_('gender')) :
                $gender = Item::findById($item->_('gender'));
            endif;
            if(\is_array($item->_('variations'))) :
                foreach ($item->_('variations') as $variation) :
                    if(
                        $variation['stockMinimal'] > 0
                        && isset($variation['stockMinimal'], $variation['stock'])
                        && $variation['stock'] < $variation['stockMinimal']
                    ) :
                        fputcsv($output, [
                            $item->_('name'),
                            strtoupper($gender->_('name')).'_'.$variation['sku'],
                            $item->_('printId'),
                            $variation['stock'],
                            $variation['stockMinimal'],
                            (int)$variation['stockMinimal'] - (int)$variation['stock']
                        ]);
                        $totalItems += (int)$variation['stockMinimal'] - (int)$variation['stock'];
                    endif;
                endforeach;
            elseif($item->_('stock')) :
                die('stock nog verder uitwerken');
            endif;

            if($totalItems > 0 ) :
                fputcsv($output, [
                    '',
                    '',
                    '',
                    'totaal',
                    $totalItems
                ]);
                fputcsv($output, []);
            endif;
        endforeach;

        parent::disableView();
    }
}
