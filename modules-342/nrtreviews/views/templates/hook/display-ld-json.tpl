{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if isset($reviews) && $reviews}"aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "{$avgReviews.avg|round:1|escape:'html':'UTF-8'}",
    "reviewCount": "{$avgReviews.nbr|escape:'html':'UTF-8'}"
},
{foreach from=$reviews item="review"}
"review":{
    "@type": "Review",
    "reviewRating": {
        "@type": "Rating",
        "ratingValue": "{$review.rating}",
        "bestRating": "5"
    },
    "author":{
        "@type": "Person",
        "name": "{$review.customer_name}",
        "reviewBody": "{$review.comment nofilter}",
        "datePublished": "{dateFormat date=$review.date_add full=0}"
    }
},
{/foreach}
{/if}