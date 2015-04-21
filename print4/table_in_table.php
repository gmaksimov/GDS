<?php

include('tcpdf/tcpdf.php');


$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->AddPage();

$html = '
	<table border="0" style="color: green">
		<tr>
			<td>FOO</td>
			<td>
				<table border="0">
					<tr>
						<td>BAR</td>
						<td>BAR</td>
					</tr>
					<tr>
						<td>BAZ</td>
						<td>BAZ</td>
					</tr>
					<tr>
						<td>
							<table border="4" style="color: red">
								<tbody>
									<tr>
										<td>QUX</td>
										<td>QUX</td>
									</tr>
									<tr>
										<td>QUX</td>
									</tr>
								</tbody>
							</table>
						</td>
					</td>
				</table>
			</td>
		</tr>
	</table>
';


$pdf->WriteHTML($html);

$pdf->Output();