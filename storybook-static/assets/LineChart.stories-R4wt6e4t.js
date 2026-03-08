import{j as e}from"./jsx-runtime-u17CrQMm.js";import{c as l}from"./utils-BQHNewu7.js";import{u as L}from"./use-reduced-motion-Bb6b8sk-.js";import{S as b}from"./skeleton-CuxgNOSS.js";import{R as T,C as p}from"./chart-colors-wMYsAnQC.js";import{L as j,a as C}from"./LineChart-DJGfz-hY.js";import{C as K,X as A,Y as D}from"./YAxis-C0ByQhTu.js";import{T as q,L as R}from"./getClassNameFromUnknown-wb-VDXTZ.js";import"./index-CAT4JDgN.js";import"./_commonjsHelpers-CE1G-McA.js";import"./index-C1GPoNty.js";import"./index-BqKNBXXg.js";import"./index-BAEXrmqL.js";import"./getRadiusAndStrokeWidthFromDot-CHDRXA91.js";import"./ErrorBarContext-X7aR2pnw.js";import"./CartesianChart-CkFwa3sR.js";import"./ActiveShapeUtils-Caemlefb.js";function s({data:r,dataKeys:c,xKey:m,curved:g=!0,showDots:f=!1,showGrid:h=!0,showLegend:x=!1,showTooltip:v=!0,skeleton:w=!1,height:i=300,className:d}){const y=L();return w?e.jsx(b,{className:l("rounded-md",d),style:{height:i}}):e.jsx("div",{className:l("w-full",d),style:{height:i},children:e.jsx(T,{width:"100%",height:"100%",children:e.jsxs(j,{data:r,margin:{top:4,right:4,bottom:0,left:0},children:[h&&e.jsx(K,{strokeDasharray:"3 3",stroke:"var(--border)",vertical:!1}),e.jsx(A,{dataKey:m,tick:{fill:"var(--muted-foreground)",fontSize:12},axisLine:{stroke:"var(--border)"},tickLine:!1}),e.jsx(D,{tick:{fill:"var(--muted-foreground)",fontSize:12},axisLine:!1,tickLine:!1}),v&&e.jsx(q,{contentStyle:{background:"var(--popover)",border:"1px solid var(--border)",borderRadius:"8px",color:"var(--popover-foreground)",fontSize:12},cursor:{stroke:"var(--border)"}}),x&&e.jsx(R,{}),c.map((u,k)=>e.jsx(C,{type:g?"monotone":"linear",dataKey:u,stroke:p[k%p.length],strokeWidth:2,dot:f?{r:3}:!1,activeDot:{r:5},isAnimationActive:!y},u))]})})})}s.__docgenInfo={description:"",methods:[],displayName:"LineChart",props:{data:{required:!0,tsType:{name:"Array",elements:[{name:"Record",elements:[{name:"string"},{name:"unknown"}],raw:"Record<string, unknown>"}],raw:"Record<string, unknown>[]"},description:""},dataKeys:{required:!0,tsType:{name:"Array",elements:[{name:"string"}],raw:"string[]"},description:""},xKey:{required:!0,tsType:{name:"string"},description:""},curved:{required:!1,tsType:{name:"boolean"},description:"",defaultValue:{value:"true",computed:!1}},showDots:{required:!1,tsType:{name:"boolean"},description:"",defaultValue:{value:"false",computed:!1}},showGrid:{required:!1,tsType:{name:"boolean"},description:"",defaultValue:{value:"true",computed:!1}},showLegend:{required:!1,tsType:{name:"boolean"},description:"",defaultValue:{value:"false",computed:!1}},showTooltip:{required:!1,tsType:{name:"boolean"},description:"",defaultValue:{value:"true",computed:!1}},skeleton:{required:!1,tsType:{name:"boolean"},description:"",defaultValue:{value:"false",computed:!1}},height:{required:!1,tsType:{name:"number"},description:"",defaultValue:{value:"300",computed:!1}},className:{required:!1,tsType:{name:"string"},description:""}}};const n=[{week:"W1",signups:120,churned:8},{week:"W2",signups:145,churned:12},{week:"W3",signups:132,churned:7},{week:"W4",signups:189,churned:14},{week:"W5",signups:210,churned:9},{week:"W6",signups:198,churned:11}],J={title:"Charts/LineChart",component:s,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{curved:{control:"boolean"},showDots:{control:"boolean"},showGrid:{control:"boolean"},showLegend:{control:"boolean"},skeleton:{control:"boolean"},height:{control:{type:"range",min:100,max:600,step:50}}}},a={args:{data:n,dataKeys:["signups"],xKey:"week",height:300},render:r=>e.jsx("div",{className:"w-[500px]",children:e.jsx(s,{...r})})},t={args:{data:n,dataKeys:["signups","churned"],xKey:"week",showLegend:!0,height:300},render:r=>e.jsx("div",{className:"w-[500px]",children:e.jsx(s,{...r})})},o={args:{data:n,dataKeys:["signups"],xKey:"week",curved:!0,showDots:!0,height:300},render:r=>e.jsx("div",{className:"w-[500px]",children:e.jsx(s,{...r})})};a.parameters={...a.parameters,docs:{...a.parameters?.docs,source:{originalSource:`{
  args: {
    data: DATA,
    dataKeys: ['signups'],
    xKey: 'week',
    height: 300
  },
  render: args => <div className="w-[500px]"><LineChart {...args} /></div>
}`,...a.parameters?.docs?.source}}};t.parameters={...t.parameters,docs:{...t.parameters?.docs,source:{originalSource:`{
  args: {
    data: DATA,
    dataKeys: ['signups', 'churned'],
    xKey: 'week',
    showLegend: true,
    height: 300
  },
  render: args => <div className="w-[500px]"><LineChart {...args} /></div>
}`,...t.parameters?.docs?.source}}};o.parameters={...o.parameters,docs:{...o.parameters?.docs,source:{originalSource:`{
  args: {
    data: DATA,
    dataKeys: ['signups'],
    xKey: 'week',
    curved: true,
    showDots: true,
    height: 300
  },
  render: args => <div className="w-[500px]"><LineChart {...args} /></div>
}`,...o.parameters?.docs?.source}}};const P=["Default","MultiLine","Curved"];export{o as Curved,a as Default,t as MultiLine,P as __namedExportsOrder,J as default};
