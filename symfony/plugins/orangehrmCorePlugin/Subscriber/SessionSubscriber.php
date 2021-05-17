<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 */

namespace OrangeHRM\Core\Subscriber;

use OrangeHRM\Framework\Event\AbstractEventSubscriber;
use OrangeHRM\Framework\Http\Session\Session;
use OrangeHRM\Framework\ServiceContainer;
use OrangeHRM\Framework\Services;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SessionSubscriber extends AbstractEventSubscriber
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onRequestEvent', 100000],
            ],
        ];
    }

    public function onRequestEvent(RequestEvent $event)
    {
        /** @var Session $session */
        $session = ServiceContainer::getContainer()->get(Services::SESSION);

        // TODO:: move to config
        $maxIdleTime = 1800;
        if (time() - $session->getMetadataBag()->getLastUsed() > $maxIdleTime) {
            $hasRedirectUri = $session->has('redirect_uri');
            $redirectUri = $session->get('redirect_uri');
            $session->invalidate();


            if (!$hasRedirectUri) {
                /** @var UrlHelper $urlHelper */
                $urlHelper = ServiceContainer::getContainer()->get(Services::URL_HELPER);
                $requestUri = $event->getRequest()->getRequestUri();
                $redirectUri = $urlHelper->getAbsoluteUrl($requestUri);
            }
            $session->set('redirect_uri', $redirectUri);
        }
    }
}
