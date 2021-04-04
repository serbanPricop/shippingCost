<?php

if (!defined('_PS_VERSION_'))
    exit;

class ShippingCost extends CarrierModuleCore
{

    public function __construct()
    {
        $this->name = 'shippingcost';
        $this->tab = 'checkout';
        $this->version = '1.0.0';
        $this->author = 'JrProgrammer';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
          'min' => '1.7',
          'max' => _PS_VERSION_
        ];

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Shipping Cost Method');
        $this->description = $this->l('Allows a manual set Shipping Cost for any Order in Backend');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('SHIPPINCOST_NAME')) {
            $this->warning = $this->l('No name provided.');
        }

    }

    public function install(){
        if (!parent::install())
            return false;
        if (!$this->installCarriers())
            return false;
        return true;
    }

    public function uninstall(): bool
    {
        if(!parent::uninstall())
            return false;
        return true;
    }

    public function installCarriers()
    {
        $carrier = new Carrier();
        $carrier->name = 'Classic Delivery';
        $carrier->active = 1;
        $carrier->deleted = 0;
        foreach (Language::getLanguages(true) as $language)
        {
            $carrier->delay[(int)$language['id_lang']] = 'Delay '.$carrier->name;
            $carrier->shipping_handling = false;
            $carrier->range_behavior = 0;
            $carrier->is_module = true;
            $carrier->shipping_external = true;
            $carrier->external_module_name = $this->name;
            $carrier->need_range = true;
        }

        if(!$carrier->add()){
            return false;
        }
        $groups = Group::getGroups(true);
        foreach($groups as $group)
        {
            Db::getInstance()
                ->insert('carrier_group',array('id_carrier' => (int)$carrier->id,'id_group' => (int)$group['id_group']));
        }

        $rangePrice = new RangePrice();
        $rangePrice->id_carrier = $carrier->id;
        $rangePrice->delimiter1 = '0';
        $rangePrice->delimiter2 = '10000';
        $rangePrice->add();

        $rangeWeight = new RangeWeight();
        $rangeWeight->id_carrier = $carrier->id;
        $rangeWeight->delimiter1 = '0';
        $rangeWeight->delimiter2 = '10000';
        $rangeWeight->add();

        $zones = Zone::getZones(true);
        foreach ($zones as $zone)
        {   Db::getInstance()->insert('carrier_zone',array('id_carrier' => (int)$carrier->id, 'id_zone' => (int)$zone['id_zone']));
            Db::getInstance()->insert('delivery',array('id_carrier' => (int)$carrier->id, 'id_range_price' => (int)$rangePrice->id, 'id_range_weight' => NULL, 'id_zone' => (int)$zone['id_zone'], 'price' => '0'));
            Db::getInstance()->insert('delivery',array('id_carrier' => (int)$carrier->id, 'id_range_price' => NULL, 'id_range_weight' => (int)$rangeWeight->id, 'id_zone' => (int)$zone['id_zone'], 'price' => '0'));
        }

        return true;
    }

    public function getContent()
    {
        if(Tools::isSubmit('shippingMethod')){
            $tax = Tools::getValue('methodTax');
            Configuration::updateValue('METHODTAX',$tax);
        }
        $this->context->smarty->assign([
            'METHODTAX' => Configuration::get('METHODTAX')
        ]);
        return $this->display(__FILE__,'views/templates/admin/configuration.tpl');
    }

    public function getOrderShippingCost($params, $shipping_cost)
    {
        return Configuration::get('METHODTAX');
    }

    public function getOrderShippingCostExternal($params)
    {
        // TODO: Implement getOrderShippingCostExternal() method.
    }
}
