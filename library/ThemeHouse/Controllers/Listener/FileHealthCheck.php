<?php

class ThemeHouse_Controllers_Listener_FileHealthCheck
{

    public static function fileHealthCheck(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
    {
        $hashes = array_merge($hashes,
            array(
                'library/ThemeHouse/Controllers/ControllerAdmin/Controller.php' => '82c7d28d4da234cafd8ea70f0a6abfa2',
                'library/ThemeHouse/Controllers/ControllerAdmin/ControllerAdmin.php' => '5b5a2c0460c730186c7ba31020bf0455',
                'library/ThemeHouse/Controllers/ControllerAdmin/ControllerPublic.php' => 'af532bad6952024c6daed016e422d7dd',
                'library/ThemeHouse/Controllers/Extend/XenForo/DataWriter/RoutePrefix.php' => '1c9d67c9489445ef45444fd59711d849',
                'library/ThemeHouse/Controllers/Extend/XenForo/Route/PrefixAdmin/AddOns.php' => '0a1cf19182d7047757a3be4050f97a0f',
                'library/ThemeHouse/Controllers/Helper/DataWriter.php' => '17e46aa47904009399dff44d5e8fe01b',
                'library/ThemeHouse/Controllers/Helper/RoutePrefix.php' => 'b50f1cb79539f7bc20e8d77c267f01ef',
                'library/ThemeHouse/Controllers/Install/Controller.php' => '2b76ed2417f07e8f57e4abfe8fedbd8f',
                'library/ThemeHouse/Controllers/Listener/LoadClass.php' => '5dc379783f150392f9e55c26b5109cfd',
                'library/ThemeHouse/Controllers/PhpFile/ControllerAdmin.php' => '805216e3a15285e3b718756344ee8fff',
                'library/ThemeHouse/Controllers/PhpFile/Route/Prefix.php' => '8304d49c2bbf1046b93a8c59a0478fc7',
                'library/ThemeHouse/Controllers/PhpFile/Route/PrefixAdmin.php' => '6e21f38a6bbff75b17a9ea9fb23d5a71',
                'library/ThemeHouse/Controllers/Reflection/Class/DataWriter.php' => 'ce407ff5715c837d02b1aba7975bf512',
                'library/ThemeHouse/Controllers/Reflection/Class/Model.php' => '9143150d11067a4debc4685dfae0b12c',
                'library/ThemeHouse/Controllers/Route/PrefixAdmin/AdminControllers.php' => '989e0d8bb11143b413d865b5d5022351',
                'library/ThemeHouse/Controllers/Route/PrefixAdmin/PublicControllers.php' => 'af6985e432f3b66a522a479db18a3c5b',
                'library/ThemeHouse/Install.php' => '18f1441e00e3742460174ab197bec0b7',
                'library/ThemeHouse/Install/20151109.php' => '2e3f16d685652ea2fa82ba11b69204f4',
                'library/ThemeHouse/Deferred.php' => 'ebab3e432fe2f42520de0e36f7f45d88',
                'library/ThemeHouse/Deferred/20150106.php' => 'a311d9aa6f9a0412eeba878417ba7ede',
                'library/ThemeHouse/Listener/ControllerPreDispatch.php' => 'fdebb2d5347398d3974a6f27eb11a3cd',
                'library/ThemeHouse/Listener/ControllerPreDispatch/20150911.php' => 'f2aadc0bd188ad127e363f417b4d23a9',
                'library/ThemeHouse/Listener/InitDependencies.php' => '8f59aaa8ffe56231c4aa47cf2c65f2b0',
                'library/ThemeHouse/Listener/InitDependencies/20150212.php' => 'f04c9dc8fa289895c06c1bcba5d27293',
                'library/ThemeHouse/Listener/LoadClass.php' => '5cad77e1862641ddc2dd693b1aa68a50',
                'library/ThemeHouse/Listener/LoadClass/20150518.php' => 'f4d0d30ba5e5dc51cda07141c39939e3',
            ));
    }
}