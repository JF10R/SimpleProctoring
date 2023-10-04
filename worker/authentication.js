import handleRedirect from './redirect.js';
import apiRouter from './router.js';

// Replace these values with your Azure AD app's values
const clientId = 'your-client-id';
const tenantId = 'your-tenant-id';
const redirectUri = 'https://your-worker-url.com/authentication';
const authorizationEndpoint = `https://login.microsoftonline.com/${tenantId}/oauth2/v2.0/authorize`;

// The fetch handler is invoked when this worker receives a HTTP(S) request
// and should return a Response (optionally wrapped in a Promise)
async function fetch(request, env, ctx) {
  // You'll find it helpful to parse the request.url string into a URL object. Learn more at https://developer.mozilla.org/en-US/docs/Web/API/URL
  const url = new URL(request.url);

  // You can get pretty far with simple logic like if/switch-statements
  if (url.pathname === '/authenticate' || url.pathname === '/isAuthenticated') {
    switch (url.pathname) {
      case '/authenticate':
        // Get the authorization code from the query parameters
        const code = url.searchParams.get('code');

        if (!code) {
          // Redirect the user to the Azure AD authorization endpoint to initiate the authentication flow
          const redirectUrl = getAuthorizationUrl();
          return Response.redirect(redirectUrl);
        }

        // Exchange the authorization code for an access token
        const tokenResponse = await exchangeAuthorizationCodeForToken(code);

        // Return the access token as a response
        return new Response(tokenResponse.access_token, {
          headers: { 'Content-Type': 'text/plain' }
        });
      case '/isAuthenticated':
        // Check if the request contains an Authorization header with a bearer token
        const authHeader = request.headers.get('Authorization');
        if (!authHeader || !authHeader.startsWith('Bearer ')) {
          return new Response('Unauthorized', { status: 401 });
        }

        // Extract the token from the Authorization header
        const token = authHeader.substring('Bearer '.length);

        // Verify the token using the Microsoft Graph API
        const user = await verifyToken(token);

        // Return the user object as a response
        return new Response(JSON.stringify(user), {
          headers: { 'Content-Type': 'application/json' }
        });
    }
  }

  return apiRouter.handle(request);
}

function getAuthorizationUrl() {
  const params = new URLSearchParams({
    client_id: clientId,
    response_type: 'code',
    redirect_uri: redirectUri,
    scope: 'openid profile email User.Read',
    response_mode: 'query'
  });

  const url = new URL(authorizationEndpoint);
  url.search = params.toString();

  return url.toString();
}

async function exchangeAuthorizationCodeForToken(code) {
  const params = new URLSearchParams({
    grant_type: 'authorization_code',
    client_id: clientId,
    code: code,
    redirect_uri: redirectUri
  });

  const response = await fetch(tokenEndpoint, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: params
  });

  if (!response.ok) {
    throw new Error(`Failed to exchange authorization code for token: ${response.status} ${response.statusText}`);
  }

  return response.json();
}

addEventListener('fetch', event => {
  event.respondWith(fetch(event.request));
});