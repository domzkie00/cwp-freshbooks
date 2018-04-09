<h1>FreshBooks</h1>
<ul class="paginate-tables">
	<li class="tab-link active" data-target="freshbooks-invoices-table">Invoices</li>
	<li class="tab-link" data-target="freshbooks-estimates-table">Estimates</li>
</ul>
<div class="tab-content" data-target="freshbooks-invoices-table">
	<table class="table table-striped table-bordered" style="width:100%">
	    <thead>
	        <tr>
	            <th style="text-align: center;">Invoice</th>
	            <th style="text-align: center;">Client Name</th>
	            <th style="text-align: center;">Description</th>
	            <th style="text-align: center;">Date</th>
	            <th style="text-align: center;">Total</th>
	            <th style="text-align: center;">Status</th>
	        </tr>
	    </thead>
	    <tbody>
	    	<?php 
				if($invoices) {
					if(isset($invoices['invoice'][0])) {
						foreach($invoices['invoice'] as $invoice) {
							?>
							<tr class="one-invoice">
					            <td><a href="https://<?= $app_domain ?>.freshbooks.com/showInvoice?invoiceid=<?= $invoice['invoice_id'] ?>" target="_blank"><?= $invoice['number'] ?></a></td>
					            <td><?= $invoice['organization'] ?></td>
					            <td><?= is_string($invoice['notes']) ? $invoice['notes'] : '' ?></td>
					            <td><?= $invoice['created_at'] ?></td>
					            <td><?= $invoice['currency_code'] ?> <?= $invoice['amount'] ?></td>
					            <td><?= $invoice['status'] ?></td>
					        </tr>	
							<?php
						}
					} else {
						?>
						<tr class="one-invoice">
				            <td><a href="https://<?= $app_domain ?>.freshbooks.com/showInvoice?invoiceid=<?= $invoices['invoice']['invoice_id'] ?>" target="_blank"><?= $invoices['invoice']['number'] ?></a></td>
				            <td><?= $invoices['invoice']['organization'] ?></td>
				            <td><?= is_string($invoices['invoice']['notes']) ? $invoices['invoice']['notes'] : '' ?></td>
				            <td><?= $invoices['invoice']['created_at'] ?></td>
				            <td><?= $invoices['invoice']['currency_code'] ?> <?= $invoices['invoice']['amount'] ?></td>
				            <td><?= $invoices['invoice']['status'] ?></td>
				        </tr>	
						<?php
					}
				} else {
			?> 		<tr style="background-color: #d1d1d1;" class="empty-table">
			        	<td colspan="6" style="text-align: center;">No Invoices.</td>
		        	</tr>
	    	<?php
				}
	        ?>
	    </tbody>
	</table>
</div>
<div class="tab-content" data-target="freshbooks-estimates-table" style="display: none;">
	<table class="table table-striped table-bordered" style="width:100%">
	    <thead>
	        <tr>
	            <th style="text-align: center;">Estimate</th>
	            <th style="text-align: center;">Client Name</th>
	            <th style="text-align: center;">Description</th>
	            <th style="text-align: center;">Date</th>
	            <th style="text-align: center;">Total</th>
	            <th style="text-align: center;">Status</th>
	        </tr>
	    </thead>
	    <tbody>
	    	<?php 
				if($estimates) {
					if(isset($estimates['estimate'][0])) {
						foreach($estimates['estimate'] as $estimate) {
							?>
							<tr class="one-estimate">
					            <td><a href="https://<?= $app_domain ?>.freshbooks.com/showEstimate?estimateid=<?= $estimate['estimate_id'] ?>" target="_blank"><?= $estimate['number'] ?></a></td>
					            <td><?= $estimate['organization'] ?></td>
					            <td><?= is_string($estimate['notes']) ? $estimate['notes'] : '' ?></td>
					            <td><?= $estimate['date'] ?></td>
					            <td><?= $estimate['currency_code'] ?> <?= $estimate['amount'] ?></td>
					            <td><?= $estimate['status'] ?></td>
					        </tr>	
							<?php
						}
					} else {
						?>
						<tr class="one-estimate">
				            <td><a href="https://<?= $app_domain ?>.freshbooks.com/showEstimate?estimateid=<?= $estimates['estimate']['estimate_id'] ?>" target="_blank"><?= $estimates['estimate']['number'] ?></a></td>
				            <td><?= $estimates['estimate']['organization'] ?></td>
				            <td><?= is_string($estimates['estimate']['notes']) ? $estimates['estimate']['notes'] : '' ?></td>
				            <td><?= $estimates['estimate']['date'] ?></td>
				            <td><?= $estimates['estimate']['currency_code'] ?> <?= $estimates['estimate']['amount'] ?></td>
				            <td><?= $estimates['estimate']['status'] ?></td>
				        </tr>	
						<?php
					}
				} else {
			?> 		<tr style="background-color: #d1d1d1;" class="empty-table">
			        	<td colspan="6" style="text-align: center;">No Estimates.</td>
		        	</tr>
	    	<?php
				}
	        ?>
	    </tbody>
	</table>
</div>