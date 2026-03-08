import{j as e}from"./jsx-runtime-u17CrQMm.js";import{C as i}from"./checkbox-BM6uYP1H.js";import{L as m}from"./label-C0TV2pJf.js";import"./utils-BQHNewu7.js";import"./check-Bwm3_tfc.js";import"./createLucideIcon-Cq8_ABKM.js";import"./index-CAT4JDgN.js";import"./_commonjsHelpers-CE1G-McA.js";import"./index-dmJXtalC.js";import"./index-CFnpPvE6.js";import"./index-BMy6K8Gt.js";import"./index-DmKEutxk.js";import"./index-D7chfnjX.js";import"./index-CfYvYFbm.js";import"./index-C1GPoNty.js";import"./index-BqKNBXXg.js";import"./index-CBqaTKC_.js";import"./index-C5dpwcrp.js";const F={title:"Forms/Checkbox",component:i,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{disabled:{control:"boolean"},defaultChecked:{control:"boolean"}}},a={},s={args:{defaultChecked:!0}},t={args:{disabled:!0}},o={render:()=>e.jsxs("div",{className:"flex items-center gap-2",children:[e.jsx(i,{id:"terms"}),e.jsx(m,{htmlFor:"terms",children:"I agree to the terms and conditions"})]})},c={render:()=>e.jsx("div",{className:"space-y-2",children:["React","TypeScript","Tailwind CSS","Laravel"].map(r=>e.jsxs("div",{className:"flex items-center gap-2",children:[e.jsx(i,{id:r,defaultChecked:r==="React"}),e.jsx(m,{htmlFor:r,children:r})]},r))})};a.parameters={...a.parameters,docs:{...a.parameters?.docs,source:{originalSource:"{}",...a.parameters?.docs?.source}}};s.parameters={...s.parameters,docs:{...s.parameters?.docs,source:{originalSource:`{
  args: {
    defaultChecked: true
  }
}`,...s.parameters?.docs?.source}}};t.parameters={...t.parameters,docs:{...t.parameters?.docs,source:{originalSource:`{
  args: {
    disabled: true
  }
}`,...t.parameters?.docs?.source}}};o.parameters={...o.parameters,docs:{...o.parameters?.docs,source:{originalSource:`{
  render: () => <div className="flex items-center gap-2">
            <Checkbox id="terms" />
            <Label htmlFor="terms">I agree to the terms and conditions</Label>
        </div>
}`,...o.parameters?.docs?.source}}};c.parameters={...c.parameters,docs:{...c.parameters?.docs,source:{originalSource:`{
  render: () => <div className="space-y-2">
            {['React', 'TypeScript', 'Tailwind CSS', 'Laravel'].map(tech => <div key={tech} className="flex items-center gap-2">
                    <Checkbox id={tech} defaultChecked={tech === 'React'} />
                    <Label htmlFor={tech}>{tech}</Label>
                </div>)}
        </div>
}`,...c.parameters?.docs?.source}}};const R=["Default","Checked","Disabled","WithLabel","CheckboxGroup"];export{c as CheckboxGroup,s as Checked,a as Default,t as Disabled,o as WithLabel,R as __namedExportsOrder,F as default};
