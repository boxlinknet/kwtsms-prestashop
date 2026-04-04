{*
 * kwtSMS - Logs Tab Template
 *
 * Renders: Filter bar, log results table with pagination,
 * and Clear All Logs button.
 *}

{* Flash messages *}
{if isset($logs_message)}
  <div class="alert alert-{$logs_message_type|escape:'html':'UTF-8'}">
    {$logs_message|escape:'html':'UTF-8'}
  </div>
{/if}

{* ============================================================ *}
{* 1. Filter Bar                                                 *}
{* ============================================================ *}
<div class="panel">
  <div class="panel-heading">
    <i class="icon-filter"></i> Filter Logs
  </div>
  <div class="panel-body">
    <form method="get" action="{$admin_link_raw|escape:'html':'UTF-8'}">
      <input type="hidden" name="controller" value="AdminKwtsms" />
      <input type="hidden" name="token" value="{$admin_token|escape:'html':'UTF-8'}" />
      <input type="hidden" name="tab" value="logs" />

      <div class="row">
        <div class="col-md-2">
          <div class="form-group">
            <label for="filter_status">Status</label>
            <select id="filter_status" name="filter_status" class="form-control">
              <option value="">All</option>
              <option value="sent"{if $filter_status == 'sent'} selected="selected"{/if}>Sent</option>
              <option value="failed"{if $filter_status == 'failed'} selected="selected"{/if}>Failed</option>
              <option value="skipped"{if $filter_status == 'skipped'} selected="selected"{/if}>Skipped</option>
            </select>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label for="filter_event_type">Event Type</label>
            <select id="filter_event_type" name="filter_event_type" class="form-control">
              <option value="">All</option>
              {foreach from=$event_types item=et}
                <option value="{$et|escape:'html':'UTF-8'}"{if $filter_event_type == $et} selected="selected"{/if}>
                  {$et|escape:'html':'UTF-8'}
                </option>
              {/foreach}
            </select>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label for="filter_date_from">Date From</label>
            <input type="date" id="filter_date_from" name="filter_date_from"
                   class="form-control"
                   value="{$filter_date_from|escape:'html':'UTF-8'}" />
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label for="filter_date_to">Date To</label>
            <input type="date" id="filter_date_to" name="filter_date_to"
                   class="form-control"
                   value="{$filter_date_to|escape:'html':'UTF-8'}" />
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label for="filter_search">Phone Search</label>
            <input type="text" id="filter_search" name="filter_search"
                   class="form-control"
                   placeholder="Phone number"
                   value="{$filter_search|escape:'html':'UTF-8'}" />
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn-primary btn-block" style="background-color: #FFA200; border-color: #FFA200;">
              <i class="icon-search"></i> Filter
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

{* ============================================================ *}
{* 2. Results Table                                              *}
{* ============================================================ *}
<div class="panel">
  <div class="panel-heading">
    <i class="icon-list"></i> SMS Logs
    <span class="badge" style="background-color: #FFA200; margin-left: 8px;">{$logs_total|intval}</span>

    {* Clear All button *}
    <form method="post" action="{$admin_link|escape:'html':'UTF-8'}&amp;tab=logs"
          style="display: inline; float: right;"
          onsubmit="return confirm('Are you sure you want to delete ALL log entries? This cannot be undone.');">
      <input type="hidden" name="submitKwtsms" value="1" />
      <input type="hidden" name="action" value="clear_logs" />
      <button type="submit" class="btn btn-danger btn-xs">
        <i class="icon-trash"></i> Clear All Logs
      </button>
    </form>
  </div>
  <div class="panel-body">
    {if $logs|@count == 0}
      <div class="alert alert-info">No log entries found.</div>
    {else}
      <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
          <thead>
            <tr>
              <th>Date</th>
              <th>Phone</th>
              <th>Event</th>
              <th>Status</th>
              <th>Error</th>
              <th>Message</th>
              <th>Test</th>
            </tr>
          </thead>
          <tbody>
            {foreach from=$logs item=log}
              <tr>
                <td style="white-space: nowrap; font-size: 12px;">
                  {$log.date_add|escape:'html':'UTF-8'}
                </td>
                <td>{$log.recipient|escape:'html':'UTF-8'}</td>
                <td>
                  <span class="label label-default">{$log.event_type|escape:'html':'UTF-8'}</span>
                </td>
                <td>
                  {if $log.status == 'sent'}
                    <span class="label-kwtsms-ok">Sent</span>
                  {elseif $log.status == 'failed'}
                    <span class="label-kwtsms-err">Failed</span>
                  {elseif $log.status == 'skipped'}
                    <span class="label-kwtsms-warn">Skipped</span>
                  {else}
                    <span class="label label-default">{$log.status|escape:'html':'UTF-8'}</span>
                  {/if}
                </td>
                <td>
                  {if $log.error_code}
                    <span class="text-danger">{$log.error_code|escape:'html':'UTF-8'}</span>
                  {else}
                    <span class="text-muted">-</span>
                  {/if}
                </td>
                <td style="max-width: 250px;">
                  <span title="{$log.message|escape:'html':'UTF-8'}">
                    {$log.message|truncate:50:'...'|escape:'html':'UTF-8'}
                  </span>
                </td>
                <td>
                  {if $log.test_mode}
                    <span class="label-kwtsms-warn">Test</span>
                  {/if}
                </td>
              </tr>
            {/foreach}
          </tbody>
        </table>
      </div>

      {* Pagination *}
      {if $logs_pages > 1}
        <nav class="text-center">
          <ul class="pagination">
            {if $logs_page > 1}
              <li>
                <a href="{$logs_page_url|escape:'html':'UTF-8'}&amp;logs_page={$logs_page - 1}">&laquo; Prev</a>
              </li>
            {/if}

            {section name=p start=1 loop=$logs_pages+1}
              <li{if $smarty.section.p.index == $logs_page} class="active"{/if}>
                <a href="{$logs_page_url|escape:'html':'UTF-8'}&amp;logs_page={$smarty.section.p.index}">
                  {$smarty.section.p.index}
                </a>
              </li>
            {/section}

            {if $logs_page < $logs_pages}
              <li>
                <a href="{$logs_page_url|escape:'html':'UTF-8'}&amp;logs_page={$logs_page + 1}">Next &raquo;</a>
              </li>
            {/if}
          </ul>
        </nav>
      {/if}

    {/if}
  </div>
</div>
