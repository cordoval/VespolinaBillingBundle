<?php
/**
 * (c) 2012 Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\BillingBundle;

use Vespolina\Entity\OrderInterface;

/**
 * An interface to manage the creation of billing requests
 *
 * @author Daniel Kucharski <daniel-xerias.be>
 */
interface BillingRequestManagerInterface
{
		/*
     * Create a new billing request
     * 
		 * 
     * @return \Vespolina\BillingBundle\BillingRequestInterface
     */
		function createBillingRequest(PartnerInterface $partner);
	
}
