# blog
Blog bundle for sandbox


Command line to install blog and dependencies:

        php composer.phar require sebardo/ecommerce:dev-master
        
Or you can try with this steps (virtual host for blog.dev already created)

        1- Install symfony and execute: symfony new ecommerce 2.8
        2- Edit your composer.json
        3- Add lines to parameter.yml and configure your database
        4- Add class to AppKernel.php
        5- Add routing
        6- Edit config.yml and config_test.yml
        7- Install and execute composer: curl -sS https://getcomposer.org/installer | php && php composer.phar update
        8- Execute build symfony command (dev and test): 
                (dev) php app/console doctrine:schema:drop --force && php app/console doctrine:schema:create && php app/console doctrine:fixtures:load 
                (test) php app/console doctrine:schema:drop --force --env=test && php app/console doctrine:schema:create --env=test && php app/console doctrine:fixtures:load --fixtures=vendor/sebardo/core/CoreBundle/DataFixtures/ORM/test/LoadCoreTestData.php --env=test
        9- And if you want develop environment edit .httacces in order to repalce app.php => app_dev.php
        10- Done!! you can try http://ecommerce.dev/login and login as "admin@admin.com" and pass "admin" or you can login as user with "user" and pass "user"

Add required to composer.json

        "sebardo/ecommerce": "dev-master"
        
Add paramters to parameter.yml

        # edit this lines
        database_name: dev_ecommerce
        database_name_test: test_ecommerce
        database_user: ecommerce
        database_password: ecommerce
        
        node_path: /usr/bin/nodejs
        node_modules_path: /usr/local/lib/node_modules
        core:
        name: Site name
        extended_layout: ''
        extended_layout_admin: 'AdminBundle:Base:layout.html.twig'
        upload_directory: uploads
        server_base_url: 'http://sitename.dev'
        fixtures_test: false
        admin_email: admin@admin.com
        company:
            id: XXX123123
            name: Company Name
            address: 'Address str. 12'
            postal_code: '14199'
            city: City Name
            country: Country
            telephone: '0157 557 58150'
            email: company@email.com
            website_url: www.sitename.com
            instagram: 'sitename'
        

Add class to AppKernel.php

        new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
        new Symfony\Bundle\AsseticBundle\AsseticBundle(),
        new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),

        new CoreBundle\CoreBundle(),
        new AdminBundle\AdminBundle(),
        new EcommerceBundle\EcommerceBundle(),
        
Add routing.yml to route file

        core:
            resource: "@CoreBundle/Resources/config/routing.yml"
            prefix:   /
            
        admin:
            resource: "@AdminBundle/Resources/config/routing.yml"
            prefix:   /
        
        blog:
            resource: "@BlogBundle/Resources/config/routing.yml"
            prefix:   /

Remove this lines in config.yml

        - { resource: security.yml }
        - { resource: services.yml }
        
And add this lines or edit this lines in config.yml and config_test.yml
        
        - { resource: "@CoreBundle/Resources/config/security.yml" }
        - { resource: "@CoreBundle/Resources/config/services.yml" }
        - { resource: "@AdminBundle/Resources/config/services.yml" }
        - { resource: "@BlogBundle/Resources/config/services.yml" }

        parameters:
            locale: es
            
        
        framework:
            ...
            # and descomment 
            translator:      { fallbacks: ["%locale%"] }
            
        # Twig Configuration
        twig:
            debug:            "%kernel.debug%"
            strict_variables: "%kernel.debug%"
            globals:
                core: %core%

        # Assetic Configuration
        assetic:
            debug:          "%kernel.debug%"
            use_controller: '%kernel.debug%'
            bundles:
                [ CoreBundle, AdminBundle, BlogBundle ]
            node: "%node_path%"
            filters:
                cssrewrite:
                    apply_to: "\.css$"
                less:
                    node: "%node_path%"
                    node_paths: ["%node_modules_path%"]
                    apply_to: "\.less$"
                  
        # OAuth login social networks  
        hwi_oauth:
             #name of the firewall in which this bundle is active, this setting MUST be set
        
            firewall_name: secured_area
            target_path_parameter: /
            resource_owners:
                twitter:
                    type:                twitter
                    client_id:           lV0OGkdpom7fu0umpyOYl69v4
                    client_secret:       i0JA4XNVvqGLED88X031208nJzydR07ek3zNOPjqWtiaoEyTsU
                google:
                    type:                google
                    client_id:           295710704391-kuvgr89k3empant281ilbk0aescnhiee.apps.googleusercontent.com
                    client_secret:       Vvo_sIbWW7mdi-vLh6tpLkMa
                    scope:               "email profile"
                    options:
                        access_type:     offline
                        approval_prompt: force
                        display:         popup
                        login_hint:      sub
                facebook:
                    type:                facebook
                    client_id:           502605306534870
                    client_secret:       8e85bc722eff0f6485ebf08abad30f5b
                    scope:               "email"
                    options:
                        display: popup 
            
        # Doctrine DQL funtions added
        doctrine:
            ...
            orm:
                auto_generate_proxy_classes: "%kernel.debug%"
                naming_strategy: doctrine.orm.naming_strategy.underscore
                auto_mapping: true
                dql:
                    numeric_functions:
                        DISTANCE: CoreBundle\Functions\DistanceFunction
                    string_functions:
                        GroupConcat: CoreBundle\Functions\GroupConcatFunction

        # If you want add item in admin menu use this example
        # dashboard:
        #    icon_class: 'fa fa-dashboard'
        #    label: 'dashboard'
        #    options:
        #        menuitems: core_menuitem_index
        #        sliders: core_slider_index
        admin:
            admin_menus:  ~
            apis:
                google_analytics:
                    options:
                        application_name: Analitycs integración
                        oauth2_client_id: 43533348693-s4rafifpr1o07gja2kgnfbhf4tjq2g0f.apps.googleusercontent.com
                        oauth2_client_secret: lo04F5hvUi_gPaAxyucY70jy
                        oauth2_redirect_uri: 'http://sasturain.dev/admin/analytics'
                        developer_key: AIzaSyCda_bsJ-kEa1M1DJenwKfUfyLVlVKuC6I


        ecommerce:
        #    fixture_data: true
        #    currency_symbol: €
        #    advert_unit_price: 1
        #    vat: 0.21
        #    special_percentage_charge: 0
        #    delivery_expenses_type: by_percentage
        #    delivery_expenses_percentage: 0
        #    bank_account: 'ESXX XXX XXXX XXXX XXXX XXXX'
            providers:
                braintree_dev:
                    options:
                        environment: sandbox
                        merchant_id: 3j49t8qb3h4nv9hk
                        public_key: tygwjhymnm5bm55s
                        private_key: a791f33b9d41ab9c57c857da1c526fa1
                paypal_dev:
                    options:
                        host: 'https://api.sandbox.paypal.com'
                        client_id: AafbeOnqAQTpS4bgP85kvrewollR8XsxAYmHlHI7ZzqEXqfjHMrMCaCjZjweT5y4DemLMSlfPro-P3Nz
                        secret: EJIuyFXnqYwW5HtPmPl7TsWsoCgT0-RtPnAa8TodOUGjOg9yp6E0nZOHIM5bOVP_Q1jSnTencHlxGUQ7
                        return_url: 'http://sasturain.dev/response-ok?paypal=true'
                        cancel_url: 'http://sasturain.dev/cancel-payment'
                redsys_prod:
                    options:
                        host: 'https://sis.redsys.es/sis/realizarPago'
                        secret: asdfheryar4tya4yaety
                        currency: 978
                        code: 327362570
                        terminal: 1
                        transaction_type: 0
                        bank_response_url: 'http://sasturain.dev/redsys-response'
                        return_url: 'http://sasturain.dev/response-ok?redsys=true'
                        cancel_url: 'http://sasturain.dev/cancel-payment'
                        consumer_language: 1
                redsys_dev: 
                    options:
                        host: 'https://sis-t.sermepa.es:25443/sis/realizarPago'
                        secret: qwertyasdf0123456789
                        currency: 978
                        code: 999008881
                        terminal: 4
                        transaction_type: 0
                        bank_response_url: 'http://sasturain.dev/redsys-response'
                        return_url: 'http://sasturain.dev/response-ok?redsys=true'
                        cancel_url: 'http://sasturain.dev/cancel-payment'
                        consumer_language: 1
            
in config_test.yml

        # Doctrine Configuration
        doctrine:
            dbal:
                driver:   pdo_mysql
                host:     "%database_host%"
                port:     "%database_port%"
                dbname:   "%database_name_test%"
                user:     "%database_user%"
                password: "%database_password%"
                charset:  UTF8
            orm:
                auto_generate_proxy_classes: "%kernel.debug%"
                naming_strategy: doctrine.orm.naming_strategy.underscore
                auto_mapping: true
                dql:
                    numeric_functions:
                        DISTANCE: CoreBundle\Functions\DistanceFunction
                    string_functions:
                        GroupConcat: CoreBundle\Functions\GroupConcatFunction
