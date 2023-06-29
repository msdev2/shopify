<?php
namespace Msdev2\Shopify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Msdev2\Shopify\Lib\AuthRedirection;
use Msdev2\Shopify\Models\Shop;
use Shopify\Webhooks\Registry;
use Shopify\Utils;

class ShopifyController extends Controller{

    function fallback(Request $request) {
        return redirect(Utils::getEmbeddedAppUrl($request->query("host", null)) . "/" . $request->path());
        // if (Context::$IS_EMBEDDED_APP &&  $request->query("embedded", false) === "1") {
        //     if (env('APP_ENV') === 'production') {
        //         return file_get_contents(public_path('index.html'));
        //     } else {
        //         return file_get_contents(base_path('frontend/index.html'));
        //     }
        // } else {
        //     return redirect(Utils::getEmbeddedAppUrl($request->query("host", null)) . "/" . $request->path());
        // }
    }
    public function install(Request $request)
    {
        return AuthRedirection::redirect($request);
        // $shop = $request->shop;
        // $api_key = config('msdev2.shopify_api_key');
        // $scopes = config('msdev2.scopes');
        // $redirect_uri = route("msdev2.shopify.callback");
        // $shop = Utils::sanitizeShopDomain($shop);
        // if(!$shop){
        //     return redirect()->back()->withErrors(['msg'=>'invalid domain']);
        // }
        // $install_url = "https://" . $shop . "/admin/oauth/authorize?client_id=" . $api_key . "&scope=" . $scopes . "&redirect_uri=" . urlencode($redirect_uri);
        // return redirect($install_url);
    }
    public function generateToken(Request $request)
    {

        // $session = OAuth::callback(
        //     $request->cookie(),
        //     $request->query(),
        //     [CookieHandler::class, 'saveShopifyCookie'],
        // );
        // dd($session);
        $shared_secret = config("msdev2.shopify_api_secret");
        $api_key = config('msdev2.shopify_api_key');
        $params = $request->all(); // Retrieve all request parameters
        $hmac = $request->get('hmac'); // Retrieve HMAC request parameter
        $params = array_diff_key($params, array('hmac' => '')); // Remove hmac from params
        ksort($params); // Sort params lexographically

        // Compute SHA256 digest
        $computed_hmac = hash_hmac('sha256', http_build_query($params), $shared_secret);

        // Use hmac data to check that the response is from Shopify or not
        if (!hash_equals($hmac, $computed_hmac)) {
            return "NOT VALIDATED – Someone is trying to be shady!";
        }
        $host = $request->query('host');
        $shopName = Utils::sanitizeShopDomain($request->query('shop'));

        $query = array(
            "client_id" => $api_key, // Your API key
            "client_secret" => $shared_secret, // Your app credentials (secret key)
            "code" => $params['code'] // Grab the access key from the URL
        );
        // Generate access token URL
        $url = "https://" . $shopName . "/admin/oauth/access_token";
        $result = Http::post($url,$query);
        $shop = Shop::updateOrCreate(
            ['shop' => $shopName],
            ['scope' => $result->json("scope"), 'access_token' => $result->json("access_token")]
        );
        $shop->refresh();
        $redirectUrl = Utils::getEmbeddedAppUrl($host);
        if(config('msdev2.webhooks')){
            $webhooks = explode(",",config('msdev2.webhooks'));
            foreach ($webhooks as $webhook) {
                $response = Registry::register('/shopify/webhooks', $webhook, $shopName, $result->json("access_token"));
                if ($response->isSuccess()) {
                    Log::debug("Registered $webhook webhook for shop ".$shopName);
                } else {
                    Log::error( "Failed to register $webhook  webhook for shop ".$shopName." with response body: " ,[$response->getBody()]);
                }
            }
        }
        if($shop){
            $result = $shop->rest()->get('shop');
            $shop->detail = $result->getDecodedBody()["shop"];
            $shop->save();
        }
        if (config('msdev2.billing')) {
            return redirect($redirectUrl.'/plan');
        }
        return redirect($redirectUrl);
    }
    public function webhooksAction(Request $request)
    {
        Log::error("process req",[$request]);
        return $request->all();
        try {
            $response = Registry::process($request->headers->toArray(), $request->getRawBody());

            if ($response->isSuccess()) {
                Log::info("Responded to webhook!",[$response]);
                // Respond with HTTP 200 OK
            } else {
                // The webhook request was valid, but the handler threw an exception
                Log::error("Webhook handler failed with message: " . $response->getErrorMessage());
            }
        } catch (\Exception $error) {
            // The webhook request was not a valid one, likely a code error or it wasn't fired by Shopify
            Log::error($error);
        }
    }
}
?>
