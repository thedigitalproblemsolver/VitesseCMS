<?php

namespace VitesseCms\Shop\Controllers;

use VitesseCms\Core\AbstractController;

/**
 * Class AffiliationController
 */
class AffiliationController extends AbstractController
{

    /**
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function tradeTrackerAction(): void
    {
        $trackBackURL = '/';

        //! Tradetracker Direct Linking Redirect Page.
        // Set domain name on which the redirect-page runs, WITHOUT "www.".
        $domainName = str_replace('www.', '', $_SERVER['HTTP_HOST']);

        // Set tracking group ID if provided by TradeTracker.
        $trackingGroupID = '';

        // Set the P3P compact policy.
        header('P3P: CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

        if (isset($_GET['tt'])) :
            $trackingParam = explode('_', $_GET['tt']);
            $campaignID = $trackingParam[0]??'';
            $materialID = $trackingParam[1]??'';
            $affiliateID = $trackingParam[2]??'';
            $reference = $trackingParam[3]??'';
            $redirectURL = $_GET['r']??'';

            // Calculate MD5 checksum.
            $checkSum = md5('CHK_' . $campaignID . '::' . $materialID . '::' . $affiliateID . '::' . $reference);

            // Set tracking data.
            $trackingData = $materialID . '::' . $affiliateID . '::' . $reference . '::' . $checkSum . '::' . time();

            // Set regular tracking cookie.
            setcookie(
                'TT2_' . $campaignID, $trackingData,
                time() + 31536000,
                '/',
                empty($domainName) ? null : '.' . $domainName
            );

            // Set session tracking cookie.
            setcookie(
                'TTS_' . $campaignID, $trackingData,
                0,
                '/',
                empty($domainName) ? null : '.' . $domainName
            );

            // Set tracking group cookie.
            if (!empty($trackingGroupID)) :
                setcookie(
                    '__tgdat' . $trackingGroupID,
                    $trackingData . '_' . $campaignID,
                    time() + 31536000,
                    '/',
                    empty($domainName) ? null : '.' . $domainName
                );
            endif;

            // Set track-back URL.
            $trackBackURL = 'http://tc.tradetracker.net/?c=' . $campaignID .
                '&m=' . $materialID .
                '&a=' . $affiliateID .
                '&r=' . urlencode($reference) .
                '&u=' . urlencode($redirectURL);
        endif;

        $this->redirect($trackBackURL);
    }
}
