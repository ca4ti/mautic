<?php

namespace Mautic\CoreBundle\Templating\Helper;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Symfony\Component\Templating\Helper\Helper;

class GTMHelper extends Helper
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var bool
     */
    private $landingpage_enabled;

    /**
     * GTMHelper constructor.
     */
    public function __construct(CoreParametersHelper $parametersHelper)
    {
        $this->code                = $parametersHelper->get('google_tag_manager_id', '');
        $this->landingpage_enabled = $parametersHelper->get('google_tag_manager_landingpage_enabled', '');
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return bool
     */
    public function hasLandingPageEnabled()
    {
        return $this->landingpage_enabled;
    }

    /**
     * @return string
     */
    public function getHeadGTMCode()
    {
        $id = $this->code;
        $js = "
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{$id}');</script>";

        return $id ? $js : '';
    }

    /**
     * @return string
     */
    public function getBodyGTMCode()
    {
        $id = $this->code;
        $js = '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id='.$this->code.'"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';

        return $id ? $js : '';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'google_tag_manager';
    }
}
