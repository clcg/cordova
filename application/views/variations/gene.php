    <fieldset>
        <table class="gene-table">
            <thead>
                <tr>
                    <th class="header-link">&nbsp;</th>
                    <th class="top-border header-protein">HGVS protein change</th>
                    <th class="top-border header-nucleotide">HGVS nucleotide change</th>
                    <th class="top-border header-locale">Variant Locale</th>
                    <th class="top-border header-position">Genomic position (Hg19)</th>
                    <th class="top-border header-variant">Variant Type</th>
                    <th class="top-border header-disease">Phenotype</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($variations as $variation): ?>
                <tr class="$zebra showinfo" id="mutation-<?php echo $variation->variation; ?>"> <!-- $variation->variation allows for the position url to be opened -->
                    <td class="external-link"><a href="<?php echo site_url('variant/' . $variation->variation); ?>"><span>More Information &raquo;</span></a></td>
                    <td class="showinfo-popup"><a><code><?php echo format_table_cell('hgvs_protein_change', $variation->hgvs_protein_change); ?></code></a></td>
                    <td class="showinfo-popup"><code><?php echo format_table_cell('hgvs_nucleotide_change', $variation->hgvs_nucleotide_change); ?></code></td>
                    <td class="showinfo-popup"><?php echo format_table_cell('variantlocale', $variation->variantlocale); ?></td>
                    <td class="showinfo-popup"><code><?php echo format_table_cell('variation', $variation->variation); ?></code></td>
                    <td class="showinfo-popup"><?php echo format_table_cell('pathogenicity', $variation->pathogenicity); ?></td>
                    <td class="showinfo-popup"><?php echo format_table_cell('disease', $variation->disease); ?></td>
                <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </fieldset>
