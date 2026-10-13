import { registerBlockType } from '@wordpress/blocks';
import { useEffect, useState } from '@wordpress/element';
import { InspectorControls, PanelColorSettings, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, RangeControl, TextControl, Notice, Button } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import ServerSideRender from '@wordpress/server-side-render';
import metadata from '../block.json';
import './style.scss';
import './editor.scss';
function Edit({ attributes, setAttributes }) {
 const { clinicId, doctorId, reviewCount, heading, backgroundColor, buttonText } = attributes;
 const availableColors = useSelect((select) => select('core/block-editor')?.getSettings()?.colors || [], []);
 const [options, setOptions] = useState({ clinics: [], doctors: [] });
 const [loading, setLoading] = useState(true);
 const [error, setError] = useState('');
 const [refresh, setRefresh] = useState(0);
 useEffect(() => {
  let active = true;
  setLoading(true); setError(''); setOptions(current => ({ ...current, doctors: [] }));
  apiFetch({ path: `/global360blocks/v1/patient-review-options?clinic=${clinicId}` }).then(data => {
   if (active) setOptions(data);
  }).catch(() => { if (active) setError('Unable to load clinics and doctors. Check that Platform Core is active, then retry.'); })
   .finally(() => { if (active) setLoading(false); });
  return () => { active = false; };
 }, [clinicId, refresh]);
 return <div {...useBlockProps()}>
  <InspectorControls>
   <PanelColorSettings title="Background color" colors={availableColors} colorSettings={[{
    label: 'Section background', value: backgroundColor,
    onChange: value => setAttributes({ backgroundColor: value || '' }),
   }]} />
   <PanelBody title="Patient-submitted reviews">
   <TextControl label="Heading" value={heading} onChange={value => setAttributes({ heading: value })} />
   <TextControl label="Button text" value={buttonText} onChange={value => setAttributes({ buttonText: value })} help="Links to the Patient Reviews page." />
   <SelectControl label="Clinic" value={clinicId} disabled={loading || !!error} options={[{label:'All clinics',value:0}, ...options.clinics]} onChange={value => setAttributes({clinicId:Number(value), doctorId:0})} />
   <SelectControl label="Doctor" help={clinicId ? 'Only published doctors at the selected clinic.' : 'Choose a clinic to filter by doctor.'} value={doctorId} disabled={!clinicId || loading || !!error} options={[{label:'All doctors / clinic-wide reviews',value:0}, ...options.doctors]} onChange={value => setAttributes({doctorId:Number(value)})} />
   <RangeControl label="Review count" value={reviewCount} min={1} max={50} onChange={value => setAttributes({reviewCount:value})} />
   {loading && <p role="status">Loading clinic and doctor choices…</p>}
   {error && <Notice status="error" isDismissible={false}>{error}<Button variant="secondary" onClick={() => setRefresh(refresh+1)}>Retry</Button></Notice>}
   <Button variant="secondary" onClick={() => setRefresh(refresh+1)}>Refresh preview</Button>
   </PanelBody>
  </InspectorControls>
  <p>Live approved reviews · newest first. Hidden automatically when no reviews match.</p>
  <ServerSideRender key={refresh} block={metadata.name} attributes={attributes} />
 </div>;
}
registerBlockType(metadata.name, { edit: Edit, save: () => null });
