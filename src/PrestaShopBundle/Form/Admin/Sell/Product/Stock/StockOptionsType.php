<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace PrestaShopBundle\Form\Admin\Sell\Product\Stock;

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Type;

class StockOptionsType extends TranslatorAwareType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stock_location', TextType::class, [
                'label' => $this->trans('Stock location', 'Admin.Catalog.Feature'),
                'required' => false,
                'attr' => [
                    'placeholder' => $this->trans('Enter stock location', 'Admin.Catalog.Feature'),
                    'class' => 'medium-input',
                ],
                'modify_all_shops' => true,
            ])
            ->add('low_stock_threshold', NumberType::class, [
                'label' => $this->trans('Receive a low stock alert by email', 'Admin.Catalog.Feature'),
                'label_help_box' => $this->trans(
                    'The email will be sent to all users who have access to the Stock page. To modify permissions, go to Advanced Parameters > Team.',
                    'Admin.Catalog.Help',
                ),
                'constraints' => [
                    new Type(['type' => 'numeric']),
                ],
                'required' => false,
                // These two options allow to have a default data equals to zero but displayed as empty string
                'default_empty_data' => 0,
                'empty_view_data' => null,
                'modify_all_shops' => true,
                // @todo: need to trigger opening allShopscheckbox on "disabling_switch" change too.
                'disabling_switch' => true,
                'html5' => true,
                'attr' => [
                    'placeholder' => $this->trans('Enter threshold value', 'Admin.Catalog.Feature'),
                    'class' => 'small-input',
                ],
            ])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'required' => false,
            'label' => false,
        ]);
    }
}
