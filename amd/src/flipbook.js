// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * TODO describe module flipbook
 *
 * @module     mod_flipbook/flipbook
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define("mod_flipbook/flipbook", ['jquery', 'mod_flipbook/pdf'], function($, pdfjsLib) {
    return {
        init: function(pdfUrl) {
            // Set the workerSrc for pdf.js (adjust the path if needed)
            require([M.cfg.wwwroot + '/mod/flipbook/amd/build/pdf.js'], function() {
                pdfjsLib.GlobalWorkerOptions.workerSrc = M.cfg.wwwroot + '/mod/flipbook/amd/build/pdf.worker.js';
            
                // Initialize PDF loading
                var loadingTask = pdfjsLib.getDocument(pdfUrl);
                loadingTask.promise.then(function(pdf) {
                    console.log('PDF loaded');           

                    // Fetch the first page of the PDF
                    pdf.getPage(1).then(function(page) {
                        console.log('Page loaded');

                        var scale = 1.5;
                        var viewport = page.getViewport({ scale: scale });

                        // Prepare canvas using the PDF page dimensions
                        var container = document.getElementById('flipbook-container');
                        var canvas = document.createElement('canvas');
                        var context = canvas.getContext('2d');
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;
                        container.appendChild(canvas);

                        // Render the PDF page into the canvas context
                        var renderContext = {
                            canvasContext: context,
                            viewport: viewport
                        };
                        var renderTask = page.render(renderContext);
                        renderTask.promise.then(function() {
                            console.log('Page rendered');
                        });
                    });
                }, function(reason) {
                    console.error('Error loading PDF: ' + reason);
                });
            });
        }
    };
});

