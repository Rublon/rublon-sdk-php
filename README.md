# Rublon PHP SDK

## Table of Contents

1.  [Introduction](#intro)
    *   [Use cases](#intro-use-cases)
    *   [Principles of operation](#intro-how-it-works)
    *   [First steps](#intro-first-steps)
    *   [Examples' assumptions](#intro-examples)
    *   [Modifying the library](#intro-mods)
2.  [Library initialization](#initialize)
3.  [Signing in](#auth)
    *   [Example PHP code](#auth-example)
4.  [Authentication finalization](#callback)
    *   [Input params](#callback-input)
    *   [Authentication verification](#callback-verification)
    *   [Example PHP code](#callback-example)
5.  [Email2FA - simplified identity verification](#email2fa)
    *   [Principles of operation](#email2fa-how-it-works)
    *   [Example usage](#email2fa-config)
6.  [Passwordless Login](#pslogin)
7.  [Changelog](#changelog)

<a id="intro"></a>
## 1\. Introduction

The _Rublon PHP SDK_ library is a client-side implementation of the [Rublon](https://rublon.net) authentication service written in PHP, including methods for embedding the service's GUI in a HTML-based environment. It forms a convenient PHP coding language facade for the service's REST interface.

<a id="intro-use-cases"></a>

### Use cases

Rublon provides an additional secury layer:

1.  **during logging in to your system**, adding a second (or additional) authentication factor,
2.  **while conducting a security-sensitive transactions**, providing a user the means for identity confirmation before changing passwords or conducting a money transfer.

To be able to perform an additional authentication using Rublon, the user must first be authenticated in a different way, e.g. with a username and password. It is a necessary step, because upon Rublon's initialization the service must receive certain information about the user:

*   a unique Id, stored in the system (hereinafter called **the integrated system**) implementing the Rublon service,
*   the user's email address.

To experience the full measure of two-factor authentication, the end-user should install the Rublon mobile app, available on all leading smartphone systems. However, having those with older phone devices in mind or those who do not want to install any additional apps on their phones, we prepared a [Email2FA process](#email2fa), which does not require using an additional device of any kind.

<a id="intro-how-it-works"></a>

### Principles of operation

#### User protection

User protection is active, when a user's email address in the integrated system can be matched to a user in the Rublon service. For this purpose, the user's email is sent to Rublon servers.

1.  If the email is matched to an existing Rublon account, the user's identity can be confirmed using Rublon.
2.  Otherwise, if the user does not possess a Rublon account (the email could not be matched), Rublon will use the Email2FA process, trying to verify the user's identity by sending a confirmation email message to his email address.

#### Identity confirmation

If the library finds an active user protection, a URL address pointing to Rublon servers will be generated. The user's web browser must be then redirected to that URL in order to carry out the identity confirmation.

If the web browser is the user's Trusted Device, the authentication will be performed automatically and invisibly. Otherwise, the user will be asked to verify his account in one of the following ways:

*   click the verification link sent to his email address,
*   enter the TOTP code (_Time-Based One Time Password_) using the Rublon mobile app,
*   scan a QR code using the Rublon mobile app,
*   confirm transaction by using push notification,
*   copy the verification code from SMS sent to his mobile number.

#### Return to the integrated system

After a successful authentication, the web browser will be redirected to a callback URL address, which points to the integrated system servers. The integrated system should intercept that URL, retrieve its params and finalize the authentication using this library.

<a id="intro-first-steps"></a>

### First steps

To start using the **Rublon PHP SDK** library you should:

*   install the Rublon mobile app on your smartphone, create a new account and confirm your email address,
*   visit the [Rublon Admin Console](https://admin.rublon.net) and log in,
*   go to the "Add the application" form (Applications -> Add) and fill in the required fields,
*   copy the provided **system token** and **secret key**, which will be used to identify the integrated system and verify the authenticity and integrity of the messages exchanged with Rublon API.

<a id="intro-examples"></a>

### Examples' assumptions

In the following examples we assume the existence of the superglobal session associative array `$_SESSION`, which has access to an object storing the currently logged in user data.

<a id="intro-mods"></a>

### Modifying the library

The `Rublon` class implements a few public methods, which, when needed, can be overriden with inheritance.

We strongly discourage you from modifying any part of the library, as it usually leads to difficulties during future library updates. If you need to change the flow or internal structure of the `Rublon` or `RublonCallback` classes, don't hesitate to subclass them according to your needs.

<a id="initialize"></a>

## 2\. Library initialization

To initialize the library you need to instantiate a `Rublon` class object. Its constructor takes three arguments.

<table><caption>`Rublon` class constructor arguments</caption>

<thead>

<tr>

<th>Name</th>

<th>Type</th>

<th>Description</th>

</tr>

</thead>

<tbody>

<tr>

<td>`$systemToken`</td>

<td>string</td>

<td>System token</td>

</tr>

<tr>

<td>`$secretKey`</td>

<td>string</td>

<td>Secret key</td>

</tr>

<tr>

<td>`$apiServer`</td>

<td>string</td>

<td>Rublon API Server URI</td>

</tr>

</tbody>

</table>

An example of the library's initialization in PHP:

        require_once "libs/Rublon/Rublon.php";

            $rublon = new Rublon(
                "A69FC450848B4B94A040416DC4421523",
                "bLS6NDP7pGjg346S4IHqTHgQQjjSLw3CyApvz5iRjYzgIPN4e9EOi1cQJLrTlvLoHY8zeqg4ILrItYidKJ6JjEUZaA6pR1tZMwSZ",
                "https://core.rublon.net"
            );

<a id="auth"></a>

## 3\. Signing in

Rublon protects users during their signing in processes. Even if a someone lears the user's password with malicious intent, such a person would be unable to log in to the user's account, because a physical access to the Rublon mobile app (installed in the user's smartphone) or to his email account is needed.

Administrator can force users to authenticate using the mobile app (to avoid the Email2FA process).

Authenticating a user with the second factor should be initiated, when the user has successfully passed the first factor of authentication (e.g. the valid user credentials have been provided) and the user's unique Id and email address are known.

The `Rublon::auth()` method will check the user's protection status (using the email address) and return a URL address for the web browser to be redirected to (if user protection is active).

<table><caption>`Rublon::auth()` method arguments</caption>

<thead>

<tr>

<th>Name</th>

<th>Type</th>

<th>Description</th>

</tr>

</thead>

<tbody>

<tr>

<td>`$callbackUrl`</td>

<td>string</td>

<td>The integrated system's callback URL</td>

</tr>

<tr>

<td>`$appUserId`</td>

<td>string</td>

<td>The integrated system's user's unique ID, which will allow to log in the user upon successful authentication</td>

</tr>

<tr>

<td>`$userEmail`</td>

<td>string</td>

<td>The user's email address in the integrated system, which will allow to check the user's protection status and match the user to a Rublon account</td>

</tr>

<tr>

<td>`$consumerParams`</td>

<td>array</td>

<td>Additional transaction parameters (optional)</td>

</tr>

<tr>

<td>`$isPasswordless`</td>

<td>boolean</td>

<td>Information if it is a login attempt using passwordless method (optional)</td>

</tr>

</tbody>

</table>

<a id="auth-example"></a>

### Example PHP code

An example of logging in a user on an integrated system:

    /**
         * An example method used to log the user in (integrated system's method)
         *
         * @param string $login
         * @param string $password
         */
        function login($login, $password) {
            if (loginPreListener()) {
                if ($user = authenticate($login, $password)) {
                    // The user has been authenticated.
                    $_SESSION["user"] = $user;
                    loginPostListener();
                }
            }
        }

        /**
         * Listener (hook) invoked after a successful first factor user authentication,
         * implemented for Rublon integration purposes.
         */
        function loginPostListener() {

            // Make sure that the user is not logged-in
            unset($_SESSION['user']);

            $rublon = new Rublon(
                "A69FC450848B4B94A040416DC4421523",
                "bLS6NDP7pGjg346S4IHqTHgQQjjSLw3CyApvz5iRjYzgIPN4e9EOi1cQJLrTlvLoHY8zeqg4ILrItYidKJ6JjEUZaA6pR1tZMwSZ",
                "https://core.rublon.net"
            );

            try { // Initiate a Rublon authentication transaction
                $url = $rublon->auth(
                    $callbackUrl = "http://example.com?rublon=callback",
                    $_SESSION["user"]["id"], // App User ID
                    $_SESSION["user"]["email"] // User email
                );

                if (!empty($url)) { // User protection is active
                    // Redirect the user's web browser to Rublon servers to verify the protection:
                    header('Location: ' . $url);
                } else {
                    // User is not protected by Rublon, so bypass the second factor.
                    header('Location: index.php');
                }
            } catch (UserBypassedException $e) {
                // User bypassed
                header('Location: ./');
            } catch (RublonException $e) {
                // An error occurred
                die($e->getMessage());
            }
        }

If the user's account is protected by Rublon, calling the `Rublon::auth()` method will return a URL address pointing to Rublon servers, which the user's browser should redirect to in order to verify a Trusted Device and user identity by using Rublon mobile app or his email.

Because the user's web browser will be redirected to Rublon servers in order to confirm the user's identity, the user should be logged out (if he/she was logged in before) to prevent creating a user session. Otherwise Rublon will not protect the user effectively, because returning to the integrated system before a proper Rublon authentication is performed may grant the user access to an active logged in session in the system. The user should be logged in only after a successful Rublon authentication.

<a id="callback"></a>

## 4\. Authentication finalization

After a successful authentication, Rublon will redirect the user's browser to the callback URL. The callback flow continues the authentication process, i.e. the finalization of the authentication (logging in or identity confirmation).

<a id="callback-input"></a>

### Input params

The callback URL will receive its input arguments in the URL address itself (_query string_).

<table><caption>Callback URL arguments</caption>

<thead>

<tr>

<th>Name</th>

<th>Type</th>

<th>Description</th>

</tr>

</thead>

<tbody>

<tr>

<td>`state`</td>

<td>string</td>

<td>Authentication result: `ok`, `error` or `logout`</td>

</tr>

<tr>

<td>`token`</td>

<td>string</td>

<td>Access token (60 alphanumeric characters, upper- and lowercase), which allows authentication's verification using a background Rublon API connection</td>

</tr>

</tbody>

</table>

<div class="block">Notice: If the callback URL has been set to e.g. `http://example.com/auth`, the params will be appended to the URL address:

    http://example.com/auth?state=ok&token=Kmad4hAS...d

If your callback URL should be formed differently (e.g. when using mod_rewrite), you can set the callback URL's template using the meta-tags: `%token%` and `%state%`, like so:  

    http://example.com/auth/%state%/%token%

</div>

<a id="callback-verification"></a>

### Authentication verification

After the callback is invoked, for proper finalization of the authentication process you need to instantiate a `RublonCallback` class object.

<table><caption>`RublonCallback` class constructor method arguments</caption>

<thead>

<tr>

<th>Name</th>

<th>Type</th>

<th>Description</th>

</tr>

</thead>

<tbody>

<tr>

<td>`$rublon`</td>

<td>Rublon</td>

<td>An instance of the `Rublon` class.</td>

</tr>

</tbody>

</table>

Next, the `RublonCallback::call()` method should be called. It takes two arguments:

<table><caption>`RublonCallback::call()` method arguments</caption>

<thead>

<tr>

<th>Name</th>

<th>Type</th>

<th>Description</th>

</tr>

</thead>

<tbody>

<tr>

<td>`$successHandler`</td>

<td>callable</td>

<td>Name of a function/method, or an anonymous function/closure, which will be invoked on successful verification of the process, finalizing the authentication (logging the user in or confirming the user's identity for some operation).</td>

</tr>

<tr>

<td>`$cancelHandler`</td>

<td>callable</td>

<td>Name of a function/method, or an anonymous function/closure, which will be invoked on cancel `RublonCallback:call()` method to cancel the authentication transaction.</td>

</tr>

</tbody>

</table>

<table><caption>Arguments of the `$successHandler` function, passed to the `RublonCallback::call()` method</caption>

<thead>

<tr>

<th>Name</th>

<th>Type</th>

<th>Description</th>

</tr>

</thead>

<tbody>

<tr>

<td>`$appUserId`</td>

<td>string</td>

<td>The user's unique ID in the integrated system, given as an argument to the `Rublon::auth()` method, whose authentication is being confirmed by Rublon</td>

</tr>

<tr>

<td>`$callback`</td>

<td>RublonCallback</td>

<td>An instance of the `RublonCallback` class</td>

</tr>

</tbody>

</table>

<table><caption>Arguments of the `$cancelHandler` function, passed to the `RublonCallback::call()` method</caption>

<thead>

<tr>

<th>Name</th>

<th>Type</th>

<th>Description</th>

</tr>

</thead>

<tbody>

<tr>

<td>`$callback`</td>

<td>RublonCallback</td>

<td>An instance of the `RublonCallback` class</td>

</tr>

</tbody>

</table>

<a id="callback-example"></a>

### Example PHP code

An example of the `RublonCallback` class usage in the callback:

    $rublon = new Rublon(
            "A69FC450848B4B94A040416DC4421523",
            "bLS6NDP7pGjg346S4IHqTHgQQjjSLw3CyApvz5iRjYzgIPN4e9EOi1cQJLrTlvLoHY8zeqg4ILrItYidKJ6JjEUZaA6pR1tZMwSZ",
            "https://code.rublon.net"
        );

        try {
            $callback = new RublonCallback($rublon);

            $callback->call(
                $successHandler = function($appUserId, RublonCallback $callback) {
                    // The user is finally logged in
                    $_SESSION["user"] = $appUserId;
                },
                $cancelHandler = function(RublonCallback $callback) {
                    // Cancel the authentication process
                    header("Location: ./login");
                    exit;
                }
            );

            // The authentication process was successful, redirect to the main page:
            header("Location: ./");
            exit;
        } catch (RublonException $e) {
            // Please handle this error in the better way
            die($e->getMessage());
        }

<a id="email2fa"></a>

## 5\. Email2FA - simplified identity verification

For users of an integrated system who do not possess a Rublon account (they do not want to sign up or don't own a smartphone), Rublon provides a simplified form of two-factor identity verification. This feature employs an email message with an identity confirmation link sent to the email address of the user being authenticated, assuming that no one but that user has access to his/her email inbox.

This feature is enabled by default. However developer can force users to authenticate using the mobile app, to avoid the Email2FA process, which can increase the security.

<a id="email2fa-how-it-works"></a>

### Principles of operation

1.  Rublon looks for a Trusted Device, which will authenticate the user automatically.
2.  If a Trusted Device cannot be found, Rublon will check if a user with an email address provided by the integrated system is protected by Rublon. If such a user is found, the process involves using the mobile app.
3.  If no user is found (the user does not have a Rublon account), the Email2FA process is started.
4.  The user will receive an email with a identity confirmation link.
5.  After clicking the link, the user will be asked if the current browser should become a Trusted Device (signing in).
6.  In the last step, the user will be redirected to the integrated system's [callback URL](#callback) and logged in (or the transaction initiated by the user will be confirmed).

<a id="email2fa-config"></a>

### Example usage

The use of Email2FA is by default active and looks the same as the [Signing in process](#auth-example).

<a id="pslogin"></a>

## 6\. Passwordless Login

Passwordless Login is a way to integrate Rublon, which allows users to log into their accounts without login and password, only by scanning QR code with the Rublon App installed on a user smartphone.

<a id="changelog"></a>

## 7\. Changelog

### 2019-01-09 (v. 4.0)

First version of the README document.