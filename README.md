# Rublon PHP SDK

## Table of Contents

1. [Overview](#overview)
2. [Use Cases](#use-cases)
3. [Supported Authentication Methods](#auth-methods)
4. [Before You Start](#before-start)
    *   [Create an Application in the Rublon Admin Console](#create-app)
    *   [Optional: Install Rublon Authenticator](#install-ra)
5. [Configuration](#config)
    * [INFO: Initial Assumptions](#init-assumptions)
    * [INFO: Modifying the Library](#modifying-library)
    * [Initialize the Library](#init-library)
    * [Perform Authentication](#perform-auth)
    * [Finalize Authentication](#final-auth)
6. [Laravel Configuration](#laravel-config)
7. [Troubleshooting](#troubleshooting)

<a id="overview"></a>
## 1\. Overview

The _Rublon PHP SDK library_ is a client-side implementation of the Rublon API written in PHP. The library includes methods for embedding the Rublon API’s GUI in an HTML-based environment. The Rublon PHP SDK forms a convenient PHP coding language facade for Rublon API’s REST interface.

<a id="use-cases"></a>

## 2\. Use Cases

Rublon adds an extra layer of security by prompting the user to authenticate using an extra authentication method such as <a href="https://rublon.com/product/mobile-push" target="_blank">Mobile Push</a>. Even if a malicious actor compromises the user's password, the hacker would not be able to log in to the user's account because the second secure factor will thwart them.

Rublon can add an extra layer of security in the following two use cases:

1. **When a user signs in to a system** (after the user enters the correct password)
2. **When a user undergoes a security-sensitive transaction** (such as changing the password or conducting a money transfer)

When a user signs in to a system, the second authentication factor should be initiated only after:
*   the user has successfully completed the first authentication factor (e.g., entered the correct password)
*   the user's unique username and email address have been gathered

<a id="auth-methods"></a>

## 3\. Supported Authentication Methods

*   <a href="https://rublon.com/product/mobile-push" target="_blank">Mobile Push</a> - approve the authentication request by tapping a push notification displayed on the Rublon Authenticator mobile app.
*   <a href="https://rublon.com/product/mobile-passcodes" target="_blank">Mobile Passcodes</a> (TOTP) - enter the TOTP code (Time-Based One Time Password) using the Rublon Authenticator mobile app.
*   <a href="https://rublon.com/product/sms-passcodes" target="_blank">SMS Passcodes</a> - enter the verification code from the SMS sent to your mobile phone number.
*   <a href="https://rublon.com/product/qr-codes" target="_blank">QR Codes</a> - scan a QR code using the Rublon Authenticator mobile app.
*   <a href="https://rublon.com/product/email-link" target="_blank">Email Links</a> - Click the verification link sent to your email address.

<a id="before-start"></a>

## 4\. Before You Start

Before you start implementing the Rublon PHP SDK library into your code, you must create an application in the Rublon Admin Console. We also recommend that you install the Rublon Authenticator mobile app for Mobile Push, Mobile Passcode, and QR Code authentication methods.

<a id="create-app"></a>

### Create an Application in the Rublon Admin Console

1. Sign up for the Rublon Admin Console. <a href="https://rublon.com/doc/admin-console/#rublon-account-registration" target="_blank">Here’s how</a>.
2. In the Rublon Admin Console, go to the **Applications** tab and click **Add Application**.
3. Enter a name for your application and then set the type to **Custom integration using PHP SDK**.
4. Click **Save** to add the new PHP SDK application in the Rublon Admin Console.
5. Copy and save the values of **System Token** and **Secret Key**. You are going to need these values later.

<a id="install-ra"></a>

### Optional: Install Rublon Authenticator

For increased security of Multi-Factor Authentication (MFA), end-users are recommended to install the <a href="https://rublon.com/product/rublon-authenticator" target="_blank">Rublon Authenticator</a> mobile app.

Download the Rublon Authenticator for:

*   <a href="https://play.google.com/store/apps/details?id=com.rublon.authenticator&hl=en" target="_blank">Android</a>
*   <a href="https://apps.apple.com/us/app/rublon-authenticator/id1434412791" target="_blank">iOS</a>

After installing the mobile app, users can authenticate using the following authentication methods:

*   <a href="https://rublon.com/product/mobile-push" target="_blank">Mobile Push</a>
*   <a href="https://rublon.com/product/mobile-passcodes" target="_blank">Mobile Passcode</a>
*   <a href="https://rublon.com/product/qr-codes" target="_blank">QR Code</a>

In some cases, users may not want to install any additional apps on their phones. Also, some users own older phones that do not support modern mobile applications. These users can authenticate using one of the following authentication methods instead:

*   <a href="https://rublon.com/product/security-keys" target="_blank">WebAuthn/U2F Security Keys</a>
*   <a href="https://rublon.com/product/sms-passcodes" target="_blank">SMS Passcode</a>
*   <a href="https://rublon.com/product/email-link" target="_blank">Email Link</a>

<a id="config"></a>

## 5\. Configuration

Follow the steps below to configure Rublon PHP SDK.

<a id="init-assumptions"></a>

### INFO: Initial Assumptions

Let’s assume there is a superglobal session associative array `$_SESSION`. It has access to an object that stores user data of the currently logged-in user.

The `$_SESSION` array will be used in PHP code examples later in this document.

<a id="modifying-library"></a>

### INFO: Modifying the Library

The `Rublon` class implements a few public methods, which, when needed, can be overridden using class inheritance.

We strongly discourage you from modifying any part of the library, as it usually leads to difficulties during library updates. If you need to change the flow or internal structure of the `Rublon` or `RublonCallback` classes, do not hesitate to subclass them according to your needs.

<a id="init-library"></a>

### Initialize the Library

To initialize the Rublon PHP SDK library, you need to instantiate a `Rublon` class object. Its constructor takes three arguments.

<table>

<caption style="text-align: left">`Rublon` class constructor arguments</caption>

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

<td>The System Token value you copied from the Rublon Admin Console.</td>

</tr>

<tr>

<td>`$secretKey`</td>

<td>string</td>

<td>The Secret Key value you copied from the Rublon Admin Console.</td>

</tr>

<tr>

<td>`$apiServer`</td>

<td>string</td>

<td>Rublon API Server URI

Default: https://core.rublon.net
</td>

</tr>

</tbody>

</table>

#### Example PHP Code

      require_once "libs/Rublon/Rublon.php";
   
      $rublon = new Rublon(
         "D166A6E9996A40F0A88252432FA5E490",
         "913eda929c96cf52141b39f5717e25",
         "https://core.rublon.net"
      );

<a id="perform-auth"></a>

### Perform Authentication

The `Rublon::auth()` method uses the username to check the user's protection status and returns a URL address the user should be redirected to in their web browser.

<table>

<caption style="text-align: left">`Rublon::auth()` method arguments</caption>

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

<td>
The integrated system's callback URL.

Rublon will redirect the user to this URL after successful authentication.
</td>

</tr>

<tr>

<td>`$username`</td>

<td>string</td>

<td>The user's username, which allows the user to sign in</td>

</tr>

<tr>

<td>`$userEmail`</td>

<td>string</td>

<td>The user's email address, which allows to check the user's protection status and match the user to a Rublon account</td>

</tr>

<tr>

<td>`$params`</td>

<td>array</td>

<td>Additional transaction parameters (optional)</td>

</tr>

<tr>

<td>`$isPasswordless`</td>

<td>boolean</td>

<td>Whether the sign-in attempt is passwordless (optional and false by default)</td>

</tr>

</tbody>

</table>

#### Example PHP Code

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
                "D166A6E9996A40F0A88252432FA5E490",
                "913eda929c96cf52141b39f5717e25",
                "https://core.rublon.net"
            );

            try { // Initiate a Rublon authentication transaction
                $authUrl = $rublon->auth(
                    $callbackUrl = "http://example.com?rublon=callback",
                    $_SESSION["user"]["login"], // Username
                    $_SESSION["user"]["email"] // User email
                );

                if (!empty($authUrl)) { // User protection is active
                    // Redirect the user's web browser to Rublon servers to verify the protection:
                    header('Location: ' . $authUrl);
                } else {
                    // User is not protected by Rublon, so bypass the second factor.
                    header('Location: index.php');
                }
            } catch (UserDeniedException $e) {
                // Access Denied
                header('Location: ./');
            } catch (UserBypassedException $e) {
                // User bypassed
                header('Location: ./');
            } catch (RublonException $e) {
                // An error occurred
                die($e->getMessage());
            }
        }

**Note:** Make sure that your code checks that the user is not signed in. The user should be signed in only after successful Rublon authentication.

<a id="final-auth"></a>

### Finalize Authentication

After successful authentication, Rublon redirects the user to the callback URL. The callback flow continues and finalizes the authentication process.

#### Input Params

The callback URL will receive input arguments in the URL address itself (query string).

<table>

<caption style="text-align: left">Callback URL arguments</caption>

<thead>

<tr>

<th>Name</th>

<th>Type</th>

<th>Description</th>

</tr>

</thead>

<tbody>

<tr>

<td>`rublonState`</td>

<td>string</td>

<td>Authentication result: `ok`.</td>

</tr>

<tr>

<td>`rublonToken`</td>

<td>string</td>

<td>Access token (60 alphanumeric characters, upper- and lowercase), which allows to verify the authentication using a background Rublon API connection</td>

</tr>

</tbody>

</table>

**Note:** If the callback URL has been set to, e.g., `http://example.com/auth`, the params will be appended to the URL address:

http://example.com/auth?rublonState=ok&rublonToken=Kmad4hAS...

**Note:** If you want to construct the callback URL differently (e.g., by using mod_rewrite), you can set the callback URL's template using the meta-tags: `%rublonToken%` and `%rublonState%`, like so:

http://example.com/auth/%rublonState%/%rublonToken%

#### Handle Authentication Result

After the callback is invoked, you need to instantiate a `RublonCallback` class object for proper finalization of the authentication process.

<table>

<caption style="text-align: left">`RublonCallback` class constructor method arguments</caption>

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

<td>An instance of the `Rublon` class</td>

</tr>

</tbody>

</table>

Next, call the `RublonCallback::call()` method. It takes two arguments:

<table>

<caption style="text-align: left">`RublonCallback::call()` method arguments</caption>

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

<td>The name of the function/method, or an anonymous function/closure, to be invoked on successful authentication</td>

</tr>

<tr>

<td>`$cancelHandler`</td>

<td>callable</td>

<td>The name of the function/method, or an anonymous function/closure, to be invoked when the callback is canceled</td>

</tr>

</tbody>

</table>

<table>

<caption style="text-align: left">Arguments of the `$successHandler` function, passed to the `RublonCallback::call()` method</caption>

<thead>

<tr>

<th>Name</th>

<th>Type</th>

<th>Description</th>

</tr>

</thead>

<tbody>

<tr>

<td>`$username`</td>

<td>string</td>

<td>The user's unique username in the integrated system, that was passed as an argument to the `Rublon::auth()` method</td>

</tr>

<tr>

<td>`$callback`</td>

<td>RublonCallback</td>

<td>An instance of the `RublonCallback` class</td>

</tr>

</tbody>

</table>

<table>

<caption style="text-align: left">Arguments of the `$cancelHandler` function, passed to the `RublonCallback::call()` method</caption>

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

#### Example PHP Code

An example portraying how to use the `RublonCallback` class in the callback:

      $rublon = new Rublon(
         "D166A6E9996A40F0A88252432FA5E490",
         "913eda929c96cf52141b39f5717e25",
         "https://code.rublon.net"
      );
      
      try {
         $callback = new RublonCallback($rublon);
         $callback->call(
            $successHandler = function($username, RublonCallback $callback) {
               // The user is finally logged in
               $_SESSION["user"] = $username;
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

<a id="laravel-config"></a>

## 6\. Laravel Configuration

This Laravel configuration example uses the <a href="https://laravel.com/docs/9.x/starter-kits#laravel-breeze" target="_blank">Breeze</a> starting kit.

1. After you create the application and install Breeze, you need to add Rublon PHP SDK:

   `composer require Rublon/rublon-sdk-php`

2. Add those to .env:

   `RUBLON_TOKEN="your rublon token"`

   `RUBLON_KEY="your rublon key"`

   `RUBLON_URL="https://core.rublon.net"`

3. Create new route for Rublon callback in routes/auth.php:

   `Route::get('rublon-callback', [AuthenticatedSessionController::class, 'rublonCallback'])->name('rublon-callback');`

4. Modify the store method in the controller:

   `Http/Controllers/Auth/AuthenticatedSessionController.php`

         public function store(LoginRequest $request)
         {
            $request->authenticate();
      
            $rublon = new Rublon(
               env('RUBLON_TOKEN'),
               env('RUBLON_KEY'),
               env('RUBLON_URL'),
            );
      
            try { // Initiate a Rublon authentication transaction
               $url = $rublon->auth(
                  $callbackUrl = url('/rublon-callback'),
                  Auth::user()->email, // User email used as username
                  Auth::user()->email  // User email
               );
      
               if (!empty($url)) {
                  Auth::logout();
                  return redirect()->away($url);
               } else {
                  // User is not protected by Rublon, so bypass the second factor.
                  $request->session()->regenerate();
                  return redirect()->to('dashboard');
               }
            } catch (UserBypassedException $e) {
               return redirect()->to('login');
            } catch (RublonException $e) {
               // An error occurred
               die($e->getMessage());
            }
      
            return redirect()->intended(RouteServiceProvider::HOME);
         }

5. Add a new method for Rublon callback:

         public function rublonCallback(Request $request)
         {
            $rublon = new Rublon(
               env('RUBLON_TOKEN'),
               env('RUBLON_KEY'),
               env('RUBLON_URL'),
            );
      
            try {
               $callback = new RublonCallback($rublon);
               $request->session()->regenerate();
               $callback->call(
                  $successHandler = function($username, RublonCallback $callback) {
                     $user = User::where('email', $username)->firstOrFail();
                     Auth::login($user);
                     if (Auth::check()) {
                        return redirect()->to('dashboard');
                     } else {
                        return redirect()->to('login');
                     }
                  },
                  $cancelHandler = function(RublonCallback $callback) {
                     return redirect()->to('login');
                  }
               );

               return redirect()->to('dashboard');
            } catch (Rublon Exception $e) {
               die($e->getMessage());
            }

            return redirect()->to('dashboard');
         }

<a id="troubleshooting"></a>

## 7\. Troubleshooting

If you encounter any issues with your Rublon integration, please contact <a href="https://rublon.com/support" target="_blank">Rublon Support</a>.