<?php
namespace Scandi\Menumanager\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;

/**
 * @category Scandi
 * @package Scandi\Menumanager\Controller\Adminhtml\Menu
 * @author Dmitrijs Sitovs <dmitrijssh@majaslapa.lv / dsitovs@gmail.com>
 * @copyright Copyright (c) 2015 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *
 * Class Save
 */
class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Scandi_Menumanager::navigation_menu_item_save';

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            /** @var \Scandi\Menumanager\Model\Item $model */
            $model = $this->_objectManager->create('Scandi\Menumanager\Model\Item');

            if ($id = $this->getRequest()->getParam('item_id')) {
                $model->load($id);
            }

            $model->addData($data);

            $this->_eventManager->dispatch(
                'scandi_menumanager_item_prepare_save',
                ['menu' => $model, 'request' => $this->getRequest()]
            );

            try {
                $model->save();
                $this->messageManager->addSuccess(__('Menu item has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                $params = [
                    'menu_id' => $model->getMenuId(),
                ];

                if ($activeTab = $this->getRequest()->getParam('active_tab')) {
                    $params['active_tab'] = $activeTab;
                }

                return $resultRedirect->setPath('*/menu/edit', $params);

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving menu item.'));
            }

            $this->_getSession()->setFormData($data);

            return $resultRedirect->setPath('*/menu/edit', $params);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
