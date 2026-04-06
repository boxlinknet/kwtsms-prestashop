{*
 * kwtSMS - Admin Dashboard / Layout Wrapper
 *
 * Serves as the shared layout for all admin tabs.
 * Renders the tab navigation bar and injects {$tab_content}.
 * When current_tab == 'dashboard', also renders status and stats cards.
 *}

<style>
  :root {
    --kwtsms-orange: #FFA200;
    --kwtsms-blue:   #79CCF2;
  }

  /* Tab navigation */
  .kwtsms-tabs {
    background: #fff;
    border-bottom: 3px solid var(--kwtsms-orange);
    margin-bottom: 20px;
    padding: 0;
  }
  .kwtsms-tabs .nav-tabs {
    border-bottom: none;
    margin: 0;
    padding: 0 10px;
  }
  .kwtsms-tabs .nav-tabs > li > a {
    border-radius: 0;
    border: none;
    color: #555;
    font-weight: 600;
    padding: 14px 20px;
    transition: color 0.2s, border-bottom 0.2s;
  }
  .kwtsms-tabs .nav-tabs > li > a:hover {
    background: transparent;
    color: var(--kwtsms-orange);
    border-bottom: 3px solid var(--kwtsms-orange);
    margin-bottom: -3px;
  }
  .kwtsms-tabs .nav-tabs > li.active > a,
  .kwtsms-tabs .nav-tabs > li.active > a:hover,
  .kwtsms-tabs .nav-tabs > li.active > a:focus {
    background: transparent;
    border: none;
    border-bottom: 3px solid var(--kwtsms-orange);
    color: var(--kwtsms-orange);
    margin-bottom: -3px;
  }

  /* Module header bar */
  .kwtsms-header {
    background: #fff;
    border-left: 4px solid var(--kwtsms-orange);
    padding: 14px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
  }
  .kwtsms-header .kwtsms-logo {
    height: 32px;
    width: auto;
  }
  .kwtsms-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #333;
    letter-spacing: 0.3px;
  }
  .kwtsms-header .kwtsms-version {
    margin-left: auto;
    font-size: 12px;
    color: #999;
  }

  /* Status cards */
  .kwtsms-status-cards .panel {
    border-top: 3px solid var(--kwtsms-blue);
    border-radius: 4px;
    margin-bottom: 15px;
  }
  .kwtsms-status-cards .panel-heading {
    background: #f9f9f9;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #777;
    padding: 8px 14px;
  }
  .kwtsms-status-cards .panel-body {
    padding: 14px;
    font-size: 16px;
    font-weight: 600;
  }
  .kwtsms-status-cards .panel-body .status-value {
    display: block;
    font-size: 18px;
    font-weight: 700;
  }
  .kwtsms-status-cards .panel-body .status-sub {
    display: block;
    font-size: 11px;
    color: #aaa;
    font-weight: 400;
    margin-top: 2px;
  }

  /* Stats cards */
  .kwtsms-stats-cards .panel {
    border-top: 3px solid var(--kwtsms-orange);
    border-radius: 4px;
    margin-bottom: 15px;
    text-align: center;
  }
  .kwtsms-stats-cards .panel-heading {
    background: #fff8ee;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #999;
    padding: 8px 14px;
  }
  .kwtsms-stats-cards .panel-body {
    padding: 18px 14px;
  }
  .kwtsms-stats-cards .stat-number {
    display: block;
    font-size: 36px;
    font-weight: 700;
    color: var(--kwtsms-orange);
    line-height: 1;
  }
  .kwtsms-stats-cards .stat-label {
    display: block;
    font-size: 12px;
    color: #888;
    margin-top: 4px;
  }
  .kwtsms-stats-cards .stat-number.failed {
    color: #e74c3c;
  }

  /* Labels */
  .label-kwtsms-ok {
    background-color: #27ae60;
    color: #fff;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
  }
  .label-kwtsms-err {
    background-color: #e74c3c;
    color: #fff;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
  }
  .label-kwtsms-warn {
    background-color: #f39c12;
    color: #fff;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
  }
  .label-kwtsms-info {
    background-color: var(--kwtsms-blue);
    color: #fff;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
  }

  /* Section title */
  .kwtsms-section-title {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #aaa;
    margin: 20px 0 10px;
  }

  /* Refresh balance button area */
  .kwtsms-refresh-area {
    margin-top: 10px;
  }
</style>

<div class="kwtsms-wrapper">

  {* Header bar *}
  <div class="kwtsms-header">
    <img src="../modules/kwtsms/views/img/kwtsms_logo.png" class="kwtsms-logo" alt="kwtSMS" />
    <h2>kwtSMS</h2>
    <span class="kwtsms-version">{l s='SMS Gateway for PrestaShop' mod='kwtsms'}</span>
  </div>

  {* Tab navigation *}
  <div class="kwtsms-tabs">
    <ul class="nav nav-tabs" role="tablist">
      {foreach from=$kwtsms_tabs key=tab_key item=tab_label}
        <li role="presentation"{if $current_tab == $tab_key} class="active"{/if}>
          <a href="{$admin_link}&amp;tab={$tab_key}">{$tab_label}</a>
        </li>
      {/foreach}
    </ul>
  </div>

  {* Dashboard-only status and stats cards *}
  {if $current_tab == 'dashboard'}

    <p class="kwtsms-section-title">{l s='Gateway Status' mod='kwtsms'}</p>

    <div class="row kwtsms-status-cards">

      {* Connected *}
      <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="panel">
          <div class="panel-heading">{l s='Connection' mod='kwtsms'}</div>
          <div class="panel-body">
            {if $gateway_connected}
              <span class="status-value"><span class="label-kwtsms-ok">{l s='Connected' mod='kwtsms'}</span></span>
            {else}
              <span class="status-value"><span class="label-kwtsms-err">{l s='Disconnected' mod='kwtsms'}</span></span>
            {/if}
          </div>
        </div>
      </div>

      {* Enabled *}
      <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="panel">
          <div class="panel-heading">{l s='SMS Sending' mod='kwtsms'}</div>
          <div class="panel-body">
            {if $gateway_enabled}
              <span class="status-value"><span class="label-kwtsms-ok">{l s='Enabled' mod='kwtsms'}</span></span>
            {else}
              <span class="status-value"><span class="label-kwtsms-warn">{l s='Disabled' mod='kwtsms'}</span></span>
            {/if}
          </div>
        </div>
      </div>

      {* Test mode *}
      <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="panel">
          <div class="panel-heading">{l s='Mode' mod='kwtsms'}</div>
          <div class="panel-body">
            {if $test_mode}
              <span class="status-value"><span class="label-kwtsms-warn">{l s='Test Mode' mod='kwtsms'}</span></span>
            {else}
              <span class="status-value"><span class="label-kwtsms-ok">{l s='Live' mod='kwtsms'}</span></span>
            {/if}
          </div>
        </div>
      </div>

      {* Balance *}
      <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="panel">
          <div class="panel-heading">{l s='Balance' mod='kwtsms'}</div>
          <div class="panel-body">
            <span class="status-value" style="color: #FFA200;">{$balance|string_format:"%.2f"}</span>
            {if $balance_updated}
              <span class="status-sub">{l s='Updated:' mod='kwtsms'} {$balance_updated}</span>
            {else}
              <span class="status-sub">{l s='Never synced' mod='kwtsms'}</span>
            {/if}
            <div class="kwtsms-refresh-area">
              <a href="{$admin_link}&amp;tab=dashboard&amp;action=refresh_balance&amp;token={$smarty.get.token}"
                 class="btn btn-default btn-xs">
                <i class="icon-refresh"></i> {l s='Refresh' mod='kwtsms'}
              </a>
            </div>
          </div>
        </div>
      </div>

      {* Sender ID *}
      <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="panel">
          <div class="panel-heading">{l s='Sender ID' mod='kwtsms'}</div>
          <div class="panel-body">
            {if $sender_id}
              <span class="status-value"><span class="label-kwtsms-info">{$sender_id}</span></span>
            {else}
              <span class="status-value"><span class="label-kwtsms-err">{l s='Not set' mod='kwtsms'}</span></span>
            {/if}
          </div>
        </div>
      </div>

      {* Default country *}
      <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="panel">
          <div class="panel-heading">{l s='Default Country' mod='kwtsms'}</div>
          <div class="panel-body">
            {if $default_country}
              <span class="status-value"><span class="label-kwtsms-info">+{$default_country}</span></span>
            {else}
              <span class="status-value"><span class="label-kwtsms-err">{l s='Not set' mod='kwtsms'}</span></span>
            {/if}
          </div>
        </div>
      </div>

    </div>{* /.row .kwtsms-status-cards *}

    <p class="kwtsms-section-title">{l s='SMS Activity' mod='kwtsms'}</p>

    <div class="row kwtsms-stats-cards">

      {* Sent today *}
      <div class="col-md-4 col-sm-4 col-xs-12">
        <div class="panel">
          <div class="panel-heading">{l s='Sent Today' mod='kwtsms'}</div>
          <div class="panel-body">
            <span class="stat-number">{$sent_today}</span>
            <span class="stat-label">{l s='messages' mod='kwtsms'}</span>
          </div>
        </div>
      </div>

      {* Sent this month *}
      <div class="col-md-4 col-sm-4 col-xs-12">
        <div class="panel">
          <div class="panel-heading">{l s='Sent This Month' mod='kwtsms'}</div>
          <div class="panel-body">
            <span class="stat-number">{$sent_month}</span>
            <span class="stat-label">{l s='messages' mod='kwtsms'}</span>
          </div>
        </div>
      </div>

      {* Failed this month *}
      <div class="col-md-4 col-sm-4 col-xs-12">
        <div class="panel">
          <div class="panel-heading">{l s='Failed This Month' mod='kwtsms'}</div>
          <div class="panel-body">
            <span class="stat-number failed">{$failed_month}</span>
            <span class="stat-label">{l s='messages' mod='kwtsms'}</span>
          </div>
        </div>
      </div>

    </div>{* /.row .kwtsms-stats-cards *}

  {/if}{* end dashboard-only section *}

  {* Tab content area - renders for all tabs *}
  <div class="kwtsms-tab-content">
    {$tab_content nofilter}
  </div>

</div>{* /.kwtsms-wrapper *}
