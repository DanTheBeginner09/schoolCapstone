<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>School Fees Management System</title>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <div class="buttons-container">
      <a href="javascript:history.back()" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
        Return
    </a>
      <button id="save">Save as Image</button>
      <button id="print">Print</button>
    </div>
    
    <div class="invoice-container" id="invoice">
      <table cellpadding="0" cellspacing="0">
        <tr class="top">
          <td colspan="2">
            <table>
              <tr>
                <td class="title">
                  <img
                    src="school-logo.png"
                    style="width: 100%; max-width: 100px"
                  />
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr class="information">
          <td colspan="2">
            <table>
              <tr>
                <td>
                  ISSUED TO:<br />
                  Name: Daniel Villanueva<br />
                  EMAIL: villanuevadanzen090923@gmail.com
                </td>
                <td>
                  DATE: Jan 27, 2024<br />
                  ID NUMBER: 2024-2023<br />
                  Grade Level: 9
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr class="heading">
          <td>Description</td>
          <td>Remarks</td>
        </tr>
        <tr class="item">
          <td>Registration:</td>
          <td>500</td>
        </tr>
        <tr class="item last">
          <td>Miscellaneous:</td>
          <td>2000</td>
        </tr>
        <tr class="item last">
          <td>Downpayment:</td>
          <td>2000</td>
        </tr>
        <tr class="item last">
          <td>Tuition:</td>
          <td>2000</td>
        </tr>
        <tr class="total">
          <td></td>
          <td>Total : P385.00</td>
        </tr>
      </table>
    </div>

    <script src="html2canvas.js"></script>
    <script>
      // Function to save the invoice as an image
      document.getElementById('save').addEventListener('click', function() {
        html2canvas(document.getElementById('invoice')).then(function(canvas) {
          // Create a link element
          const link = document.createElement('a');
          // Set the link's download attribute
          link.download = 'invoice.png';
          // Convert the canvas to a data URL
          link.href = canvas.toDataURL();
          // Programmatically click the link to trigger the download
          link.click();
        });
      });

      // Function to print the invoice
      document.getElementById('print').addEventListener('click', function() {
        window.print();
      });
    </script>
  </body>
</html>
