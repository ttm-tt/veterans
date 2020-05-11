var TTMTable = {
	init : function(element) {
		if (element === undefined)
			return;

		var self = this;
		
		this._tableObserver = new MutationObserver(function(mutations) {
			mutations.forEach(function(tbody)  {
				if (tbody.addedNodes.length > 0) {
					var table = $(tbody.target).closest('table');
					self._initActions(table);
					self._initResponsive(table);
				}
			});
		});
		
		element.each(function(idx, table) {
			self._initActions($(table));
			self._initResponsive($(table));
			
			// And observe (but only one tbody)
			self._tableObserver.observe($(table).find('tbody')[0], {
				childList: true,
				subtree: false
			});

		});
		
		// At the momemnt only if there is one table only
		if (element.length === 1) {			
			self._initSelectableColumns(element);
		}
		
		// And observe
		element.each(function(idx, table) {
			$(table).find('tbody').each(function(idx, tbody) {
				self._tableObserver.observe(tbody , {
					childList: true,
					subtree: true
				});
			});
		});
		
	},
	
	_initActions : function(element) {
		var thead = element.find('thead');
		var actions = thead.find('th.actions');
		
		// Nothing to do if there are not actions
		if (actions.length === 0)
			return;
		
		// If we already have a column for the hamburger, don't increase header actons again
		if (element.find('thead th.actions-small').length === 0) {
			actions.after('<th class="actions-small">' + actions.html() + '</th>');
		}

		// Add (hidden) hamburger button to each row which has actions
		// If there are no action in this row add a placeholder
		element.find('tbody tr').each(function(idx, tr) {
			// Ignore rows which already were processed
			if ($(tr).find('td.actions-small').length === 0) {
				if ($(tr).find('td.actions a').length === 0) 
					$(this).append('<td class="actions-small"></td>');
				else
					$(this).append('<td class="actions-small"><button class="menu-icon dark" type="button" data-toggle="row-action" onclick="TTMTable.showActionsMenu(this)"></button></td>');
			}
		});
		
		// The popup (foundation: dropdown) to show on click of the hamburger
		if ($(document).find('body div#row-arcion').length === 0) {
			$(document).find('body').append(
				'<div id="row-action" class="dropdown-pane" data-dropdown data-close-on-click="true"><ul class="vertical menu simple"></ul></div>'
			);
		}
	},
	
	_initSelectableColumns : function(element) {
		// To access this in anonymous functions
		var self = this;
		
		var thead = element.find('thead');
		var tr = thead.find('tr');
		this._cols = tr.find('th.ttm-table-col');
		
		// Nothing to do if there are no appropriate columns
		if (this._cols.length === 0)
			return;

		if (sessionStorage !== undefined) {
			var value = sessionStorage.getItem(window.location.pathname + '$' + 'ttm-table');
			if (value !== null) {
				try {
					this._visible = JSON.parse(value);
				} catch (e) {
					this._visible = [];
				}
			}
		}
		
		if (this._visible.length !== this._cols.length) {
			this._visible = [];
			while (this._visible.length < this._cols.length)
				this._visible.push(true);
		}
		
		var id = 'ttm-' + Math.round(Math.random() * 10000);
		tr.prepend('<th class="ttm-col-selector"><button data-toggle="' + id + '">&hellip;</button></th>');
		
		// Append a table cell to each body row
		element.find('tbody tr').prepend('<td class="ttm-col-selector"></td>');
		
		var dd = '<div class="dropdown-pane" id = "' + id + '" ' +
				 'data-dropdown data-position="bottom" data-alignment="right" data-close-on-click="true">';
		this._cols.each(function(idx, col) {	
			if (!self._visible[idx])
				$(col).hide();
			
			self._visible[idx] = $(col).is(':visible');
			
			dd +=	'<label >';
			dd += 	'<input type="checkbox"' + ' ' + 
					(self._visible[idx] ? 'checked' : '') + ' ' +
					'onchange = "TTMTable.toggleColVisibility(' +  idx + '); return false;"' + 
					'>';
			dd +=	$(col).text() + '</label>';
		});
		dd += '</div>';
		
		$(document).find('body').append(dd);
	},
	
	_initResponsive : function(e) {
		var labels = [];
		e.find('thead th').each(function(idx, th) {
			var colspan = parseInt($(th).attr('colspan') | "1") ;
			for (var i = 0; i < colspan; i++)
				labels.push($(th).text());
		});
		
		var offset = 0;
		e.find('tbody tr').each(function(idx, tr) {
			if ($(tr).find('td.ttm-caption').length === 0) {
				// First visible td is caption
				$(tr).find('td:visible').eq(0).addClass('ttm-caption');

				$(tr).find('td').each(function(idx, td) {
					$(td).attr('data-ttm-label', labels[idx + offset]);
					offset += parseInt($(td).attr('colspan') | "1") - 1;
				});
			}
		});		
	},
	
	showActionsMenu : function(e) {
		var tr = $(e).closest('tr');
		var menu = '';
		tr.find('td.actions a').each(function() {
			menu += '<li>' + $(this)[0].outerHTML + '</li>';
		});
		
		// Fill menu
		$('#row-action ul')
				.html(menu)
		;
		
		// Anchor to current element
		$('#row-action').foundation('_setCurrentAnchor', e);
		$('#row-action ul li a').on('click', function() {
			$('#row-action').foundation('close');
			return true;
		});
		// $('#row-action').foundation('open');
	},
	
	toggleColVisibility : function (idx) {
		// We select the tbody column by the data-ttm-label attribute,
		// which is set by the initResponsive method
		var label = $(this._cols[idx]).text();
		var table = $(this._cols[idx]).closest('table');

		$(this._cols[idx]).toggle();
		table.find('tbody tr td[data-ttm-label="' + label + '"]').toggle();

		this._visible[idx] = !this._visible[idx];
		
		if (sessionStorage !== undefined)
			sessionStorage.setItem(window.location.pathname + '#' + 'ttm-table', JSON.stringify(this._visible));
	},
	
	_cols : [],
	_visible : [],
	_tableObserver : null
};