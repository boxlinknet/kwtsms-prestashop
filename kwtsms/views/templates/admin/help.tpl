{*
 * kwtSMS - Help Tab Template
 *
 * Static HTML content with setup guide, features, and support links.
 *}

<div class="row">
  <div class="col-md-8">

    {* Getting Started *}
    <div class="panel">
      <div class="panel-heading">
        <i class="icon-rocket"></i> {l s='Getting Started' mod='kwtsms'}
      </div>
      <div class="panel-body">
        <ol>
          <li>
            <strong>Create a kwtSMS account</strong> at
            <a href="https://kwtsms.com" target="_blank" rel="noopener">kwtsms.com</a>
            if you don't have one yet.
          </li>
          <li>
            <strong>Get your API credentials</strong> from the kwtSMS dashboard. You will need your
            API username and password.
          </li>
          <li>
            <strong>Register a Sender ID</strong> through your kwtSMS account. Sender IDs must be
            pre-approved before use.
          </li>
          <li>
            <strong>Add credits</strong> to your kwtSMS account. SMS sending requires available credits.
          </li>
        </ol>
      </div>
    </div>

    {* Setup Guide *}
    <div class="panel">
      <div class="panel-heading">
        <i class="icon-list-ol"></i> {l s='Setup Guide' mod='kwtsms'}
      </div>
      <div class="panel-body">
        <ol>
          <li>Go to the <strong>Gateway</strong> tab and enter your API username and password.</li>
          <li>Click <strong>Connect</strong>. The module will verify your credentials and fetch your balance, sender IDs, and coverage.</li>
          <li>Select your preferred <strong>Sender ID</strong> from the dropdown.</li>
          <li>Select the <strong>Default Country Code</strong> (e.g. +965 for Kuwait).</li>
          <li>Enable <strong>SMS Sending</strong> (master switch).</li>
          <li>Optionally enable <strong>Test Mode</strong> for initial testing (credits are recoverable).</li>
          <li>Go to the <strong>Settings</strong> tab. Enter admin phone numbers for notification alerts.</li>
          <li>Enable the integrations you want (order placed, status changes, etc.).</li>
          <li>Go to the <strong>Templates</strong> tab to customize SMS messages in English and Arabic.</li>
          <li>Set up the <strong>Cron URL</strong> from the Gateway tab to keep your balance and sender IDs in sync daily.</li>
        </ol>
      </div>
    </div>

    {* Features *}
    <div class="panel">
      <div class="panel-heading">
        <i class="icon-star"></i> {l s='Features' mod='kwtsms'}
      </div>
      <div class="panel-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>{l s='Integration' mod='kwtsms'}</th>
              <th>{l s='Description' mod='kwtsms'}</th>
              <th>{l s='Recipients' mod='kwtsms'}</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>Order Placed</strong></td>
              <td>SMS notification when a new order is placed in the shop.</td>
              <td>Customer + Admin</td>
            </tr>
            <tr>
              <td><strong>Order Status Changed</strong></td>
              <td>SMS notification when an order status is updated (shipped, delivered, etc.).</td>
              <td>Customer</td>
            </tr>
            <tr>
              <td><strong>New Customer</strong></td>
              <td>SMS welcome message when a new customer registers.</td>
              <td>Customer + Admin</td>
            </tr>
            <tr>
              <td><strong>Payment Confirmed</strong></td>
              <td>SMS notification when payment for an order is confirmed.</td>
              <td>Customer + Admin</td>
            </tr>
            <tr>
              <td><strong>Low Stock Alert</strong></td>
              <td>SMS alert when product stock drops below a configurable threshold.</td>
              <td>Admin</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    {* Sender ID *}
    <div class="panel">
      <div class="panel-heading">
        <i class="icon-tag"></i> {l s='About Sender IDs' mod='kwtsms'}
      </div>
      <div class="panel-body">
        <p>
          A Sender ID is the name or number that appears as the sender of your SMS messages.
          In Kuwait and most countries, Sender IDs must be registered and approved before use.
        </p>
        <ul>
          <li>Log in to your <a href="https://kwtsms.com" target="_blank" rel="noopener">kwtSMS dashboard</a> to register a Sender ID.</li>
          <li>Approval typically takes 1-3 business days.</li>
          <li>Once approved, the Sender ID will appear in the dropdown on the Gateway tab.</li>
          <li>Using an unapproved Sender ID will result in delivery failures.</li>
        </ul>
      </div>
    </div>

    {* Test Mode *}
    <div class="panel">
      <div class="panel-heading">
        <i class="icon-flask"></i> {l s='Test Mode' mod='kwtsms'}
      </div>
      <div class="panel-body">
        <p>
          When <strong>Test Mode</strong> is enabled, the module sends SMS through the kwtSMS API
          with the <code>test=1</code> parameter. This means:
        </p>
        <ul>
          <li>The API validates your request and returns a response as if the SMS was sent.</li>
          <li>The SMS is <strong>not actually delivered</strong> to the recipient's phone.</li>
          <li>Any credits charged are <strong>recoverable</strong> (they will be refunded).</li>
          <li>Log entries will show a "Test" badge so you can distinguish test sends from real ones.</li>
        </ul>
        <p>
          It is recommended to keep Test Mode <strong>enabled</strong> while configuring the module
          and testing your integrations. Disable it when you are ready to go live.
        </p>
      </div>
    </div>

  </div>

  {* Sidebar *}
  <div class="col-md-4">

    {* Module Info *}
    <div class="panel" style="border-top: 3px solid #FFA200;">
      <div class="panel-heading">
        <i class="icon-info-circle"></i> {l s='Module Info' mod='kwtsms'}
      </div>
      <div class="panel-body text-center">
        <img src="{$module_dir|escape:'html':'UTF-8'}views/img/kwtsms_logo.png"
             alt="kwtSMS" style="height: 48px; margin-bottom: 12px;" />
        <p><strong>kwtSMS - SMS Notifications &amp; Alerts</strong></p>
        <p class="text-muted">Version 1.0.0</p>
      </div>
    </div>

    {* Support *}
    <div class="panel" style="border-top: 3px solid #79CCF2;">
      <div class="panel-heading">
        <i class="icon-life-ring"></i> {l s='Support' mod='kwtsms'}
      </div>
      <div class="panel-body">
        <ul class="list-unstyled">
          <li style="margin-bottom: 8px;">
            <i class="icon-globe"></i>
            <a href="https://kwtsms.com" target="_blank" rel="noopener">kwtsms.com</a>
          </li>
          <li style="margin-bottom: 8px;">
            <i class="icon-envelope"></i>
            <a href="mailto:support@kwtsms.com">support@kwtsms.com</a>
          </li>
          <li style="margin-bottom: 8px;">
            <i class="icon-book"></i>
            <a href="https://kwtsms.com/developers" target="_blank" rel="noopener">{l s='API Documentation' mod='kwtsms'}</a>
          </li>
        </ul>
      </div>
    </div>

    {* Quick Links *}
    <div class="panel">
      <div class="panel-heading">
        <i class="icon-link"></i> {l s='Quick Links' mod='kwtsms'}
      </div>
      <div class="panel-body">
        <ul class="list-unstyled">
          <li style="margin-bottom: 8px;">
            <a href="{$admin_link|escape:'html':'UTF-8'}&amp;tab=gateway">
              <i class="icon-plug"></i> {l s='Gateway Settings' mod='kwtsms'}
            </a>
          </li>
          <li style="margin-bottom: 8px;">
            <a href="{$admin_link|escape:'html':'UTF-8'}&amp;tab=settings">
              <i class="icon-cogs"></i> {l s='Integration Settings' mod='kwtsms'}
            </a>
          </li>
          <li style="margin-bottom: 8px;">
            <a href="{$admin_link|escape:'html':'UTF-8'}&amp;tab=templates">
              <i class="icon-file-text"></i> {l s='SMS Templates' mod='kwtsms'}
            </a>
          </li>
          <li style="margin-bottom: 8px;">
            <a href="{$admin_link|escape:'html':'UTF-8'}&amp;tab=logs">
              <i class="icon-list"></i> {l s='View Logs' mod='kwtsms'}
            </a>
          </li>
        </ul>
      </div>
    </div>

  </div>
</div>
