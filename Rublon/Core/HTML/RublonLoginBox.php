<?php

namespace Rublon\Core\HTML;

class RublonLoginBox extends RublonWidget {

    /**
     * URL path of the login box.
     *
     * @var string
     */
    protected $urlPath = '/api/sdk/passwordless';

    /**
     * API server URL.
     *
     * @var string
     */
    protected $apiUrl;

    /**
     * Create Rublon Login Box
     * @param $apiUrl
     */
    function __construct($apiUrl) {
        $this->apiUrl = trim($apiUrl);
    }


    /**
     * Widget's HTML iframe attributes.
     *
     * @return array
     */
    protected function getWidgetAttributes() {
        return array(
            'id' => 'RublonLoginBoxWidget',
            'src' => $this->apiUrl . $this->urlPath
        );
    }

    protected function getAdditionalHTML() {
        return "<script>
                window.addEventListener('message', function(event) {
                    if (event.origin !== '". $this->apiUrl ."')
                        return;

                    if (event.data && event.data.action) {
                        var form = document.createElement('form');
                        var actionInput = document.createElement('input');
                        var userEmailInput = document.createElement('input');
                        
                        form.method = 'POST';
                        
                        actionInput.value = event.data['action'];
                        actionInput.name = 'action';
                        actionInput.type = 'hidden';
                        form.appendChild(actionInput);
                        
                        if (event.data['email']) {
                            userEmailInput.value=event.data['email'];
                            userEmailInput.name= 'userEmail';
                            userEmailInput.type = 'hidden';
                            form.appendChild(userEmailInput);
                        }
                        
                        document.body.appendChild(form);
                        
                        form.submit();
                    } else if (event.data && event.data.height) {
                        var iframe = document.querySelectorAll('iframe#RublonLoginBoxWidget');
                        for (var i = 0; i < iframe.length; i++ ) {
                            iframe[i].style.height = event.data['height'] + 38 + 'px';
                        }
                    }
                }, true);
            </script>";
    }
}