{*
 * kwtSMS - Templates Tab Template
 *
 * Renders: Template list table and edit form (when ?edit_template=ID).
 *}

{* Flash messages *}
{if isset($templates_message)}
  <div class="alert alert-{$templates_message_type|escape:'html':'UTF-8'}">
    {$templates_message|escape:'html':'UTF-8'}
  </div>
{/if}

{* ============================================================ *}
{* Edit View (when editing a specific template)                  *}
{* ============================================================ *}
{if isset($edit_template) && $edit_template}

<div class="panel">
  <div class="panel-heading">
    <i class="icon-pencil"></i> {l s='Edit Template:' mod='kwtsms'} {$edit_template.label|escape:'html':'UTF-8'}
    <a href="{$admin_link|escape:'html':'UTF-8'}&amp;tab=templates" class="btn btn-default btn-xs pull-right">
      <i class="icon-arrow-left"></i> {l s='Back to List' mod='kwtsms'}
    </a>
  </div>
  <div class="panel-body">
    <form method="post" action="{$admin_link|escape:'html':'UTF-8'}&amp;tab=templates&amp;edit_template={$edit_template.id_kwtsms_template|intval}">
      <input type="hidden" name="submitKwtsms" value="1" />
      <input type="hidden" name="id_template" value="{$edit_template.id_kwtsms_template|intval}" />

      <div class="row">
        {* English content *}
        <div class="col-md-6">
          <div class="form-group">
            <label for="content_en">{l s='English' mod='kwtsms'}</label>
            <textarea id="content_en" name="content_en"
                      class="form-control" rows="6"
                      dir="ltr">{$edit_template.content_en|escape:'html':'UTF-8'}</textarea>
          </div>
          <div class="kwtsms-placeholder-chips">
            <strong>{l s='Placeholders:' mod='kwtsms'}</strong>
            {foreach from=$edit_template.placeholders item=ph}
              <span class="kwtsms-chip">{$ph|escape:'html':'UTF-8'}</span>
            {/foreach}
          </div>
        </div>

        {* Arabic content *}
        <div class="col-md-6">
          <div class="form-group">
            <label for="content_ar">{l s='Arabic' mod='kwtsms'}</label>
            <textarea id="content_ar" name="content_ar"
                      class="form-control" rows="6"
                      dir="rtl">{$edit_template.content_ar|escape:'html':'UTF-8'}</textarea>
          </div>
          <div class="kwtsms-placeholder-chips">
            <strong>{l s='Placeholders:' mod='kwtsms'}</strong>
            {foreach from=$edit_template.placeholders item=ph}
              <span class="kwtsms-chip">{$ph|escape:'html':'UTF-8'}</span>
            {/foreach}
          </div>
        </div>
      </div>

      <div style="margin-top: 15px;">
        <button type="submit" class="btn btn-primary" style="background-color: #FFA200; border-color: #FFA200;">
          <i class="icon-save"></i> {l s='Save Template' mod='kwtsms'}
        </button>
        <a href="{$admin_link|escape:'html':'UTF-8'}&amp;tab=templates" class="btn btn-default" style="margin-left: 10px;">
          {l s='Cancel' mod='kwtsms'}
        </a>
      </div>
    </form>
  </div>
</div>

{else}

{* ============================================================ *}
{* Template List Table                                           *}
{* ============================================================ *}
<div class="panel">
  <div class="panel-heading">
    <i class="icon-file-text"></i> {l s='SMS Templates' mod='kwtsms'}
  </div>
  <div class="panel-body">
    <p class="help-block" style="margin-bottom: 15px;">
      {l s='Edit the SMS message templates for each integration. Templates support both English and Arabic. Click "Edit" to modify a template.' mod='kwtsms'}
    </p>

    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th style="width: 40%;">{l s='Template' mod='kwtsms'}</th>
            <th style="width: 15%;">{l s='Recipient' mod='kwtsms'}</th>
            <th style="width: 35%;">{l s='Preview (English)' mod='kwtsms'}</th>
            <th style="width: 10%;">{l s='Action' mod='kwtsms'}</th>
          </tr>
        </thead>
        <tbody>
          {foreach from=$templates_list item=tpl}
            <tr>
              <td>
                <strong>{$tpl.label|escape:'html':'UTF-8'}</strong>
                <br />
                <small class="text-muted">{$tpl.template_key|escape:'html':'UTF-8'}</small>
              </td>
              <td>
                {if $tpl.recipient_type == 'customer'}
                  <span class="label" style="background-color: #79CCF2;">{l s='Customer' mod='kwtsms'}</span>
                {elseif $tpl.recipient_type == 'admin'}
                  <span class="label" style="background-color: #FFA200;">{l s='Admin' mod='kwtsms'}</span>
                {/if}
              </td>
              <td>
                <small>{$tpl.content_en|truncate:80:'...'|escape:'html':'UTF-8'}</small>
              </td>
              <td>
                <a href="{$admin_link|escape:'html':'UTF-8'}&amp;tab=templates&amp;edit_template={$tpl.id_kwtsms_template|intval}"
                   class="btn btn-default btn-xs">
                  <i class="icon-pencil"></i> {l s='Edit' mod='kwtsms'}
                </a>
              </td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>
  </div>
</div>

{/if}

<style>
  .kwtsms-placeholder-chips {
    margin-top: 8px;
  }
  .kwtsms-chip {
    display: inline-block;
    background: #f0f0f0;
    border: 1px solid #ddd;
    border-radius: 3px;
    padding: 2px 8px;
    margin: 2px 4px 2px 0;
    font-size: 12px;
    font-family: monospace;
    color: #555;
    cursor: default;
  }
  .kwtsms-chip:hover {
    background: #e8e8e8;
    border-color: #ccc;
  }
</style>
