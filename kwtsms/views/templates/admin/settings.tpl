{*
 * kwtSMS - Settings Tab Template
 *
 * Renders: Admin phones input, integrations toggle table,
 * and low stock threshold field.
 *}

{* Flash messages *}
{if isset($settings_message)}
  <div class="alert alert-{$settings_message_type|escape:'html':'UTF-8'}">
    {$settings_message|escape:'html':'UTF-8'}
  </div>
{/if}

<form method="post" action="{$admin_link|escape:'html':'UTF-8'}&amp;tab=settings">
  <input type="hidden" name="submitKwtsms" value="1" />

  {* ============================================================ *}
  {* 1. Admin Phones                                               *}
  {* ============================================================ *}
  <div class="panel">
    <div class="panel-heading">
      <i class="icon-phone"></i> Admin Notification Phones
    </div>
    <div class="panel-body">
      <div class="form-group">
        <label for="kwtsms_admin_phones">Admin Phone Numbers</label>
        <input type="text" id="kwtsms_admin_phones" name="kwtsms_admin_phones"
               class="form-control" style="max-width: 500px;"
               value="{$admin_phones|escape:'html':'UTF-8'}"
               placeholder="e.g. 96598765432, 96512345678" />
        <p class="help-block">
          Comma-separated phone numbers that receive admin alerts (new orders, new customers, low stock, etc.).
          Include the country code.
        </p>
      </div>
    </div>
  </div>

  {* ============================================================ *}
  {* 2. Integrations Table                                         *}
  {* ============================================================ *}
  <div class="panel">
    <div class="panel-heading">
      <i class="icon-puzzle-piece"></i> SMS Integrations
    </div>
    <div class="panel-body">
      <p class="help-block" style="margin-bottom: 15px;">
        Enable or disable each SMS integration. When enabled, the module will send SMS messages for that event.
      </p>

      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th style="width: 40%;">Integration</th>
              <th style="width: 20%;">Recipient</th>
              <th style="width: 20%;">Status</th>
              <th style="width: 20%;">Settings</th>
            </tr>
          </thead>
          <tbody>
            {foreach from=$integrations item=integ}
              <tr>
                <td>
                  <strong>{$integ.label|escape:'html':'UTF-8'}</strong>
                  <br />
                  <small class="text-muted">{$integ.description|escape:'html':'UTF-8'}</small>
                </td>
                <td>
                  {if $integ.recipient_type == 'customer'}
                    <span class="label" style="background-color: #79CCF2;">Customer</span>
                  {elseif $integ.recipient_type == 'admin'}
                    <span class="label" style="background-color: #FFA200;">Admin</span>
                  {elseif $integ.recipient_type == 'both'}
                    <span class="label" style="background-color: #79CCF2;">Customer</span>
                    <span class="label" style="background-color: #FFA200;">Admin</span>
                  {/if}
                </td>
                <td>
                  <label class="kwtsms-toggle-label">
                    <input type="checkbox"
                           name="integration_active[{$integ.id_kwtsms_integration|intval}]"
                           value="1"
                           {if $integ.active} checked="checked"{/if} />
                    {if $integ.active}
                      <span class="label-kwtsms-ok">On</span>
                    {else}
                      <span class="label-kwtsms-err">Off</span>
                    {/if}
                  </label>
                </td>
                <td>
                  {if $integ.integration_key == 'low_stock'}
                    <div class="form-group" style="margin-bottom: 0;">
                      <div class="input-group" style="max-width: 160px;">
                        <span class="input-group-addon">Threshold</span>
                        <input type="number" name="low_stock_threshold"
                               class="form-control"
                               value="{$low_stock_threshold|intval}"
                               min="1" max="9999" />
                      </div>
                    </div>
                  {else}
                    <span class="text-muted">-</span>
                  {/if}
                </td>
              </tr>
            {/foreach}
          </tbody>
        </table>
      </div>

      <button type="submit" class="btn btn-primary" style="background-color: #FFA200; border-color: #FFA200; margin-top: 10px;">
        <i class="icon-save"></i> Save Settings
      </button>
    </div>
  </div>

</form>
